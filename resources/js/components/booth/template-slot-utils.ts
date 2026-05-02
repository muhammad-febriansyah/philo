export interface SlotPosition {
    x: number;
    y: number;
    width: number;
    height: number;
}

export interface TemplateSlotSource {
    frame_path: string | null;
    thumbnail_path: string | null;
    photo_slots: number;
    slot_positions: SlotPosition[] | null;
    print_size: string;
}

export function canvasDimensions(printSize: string): { W: number; H: number } {
    switch (printSize?.toLowerCase()) {
        case 'strip':
            return { W: 600, H: 1800 };
        case 'a3':
            return { W: 1000, H: 1414 };
        case 'a4':
        default:
            return { W: 1000, H: 1414 };
    }
}

export function generateDefaultSlots(
    count: number,
    width: number,
    height: number,
    printSize: string,
): SlotPosition[] {
    const isStrip = printSize?.toLowerCase() === 'strip';
    const padding = isStrip ? 40 : 60;
    const gap = isStrip ? 16 : 20;

    const cols = isStrip ? 1 : count <= 2 ? count : Math.ceil(Math.sqrt(count));
    const rows = Math.ceil(count / cols);
    const slotW = (width - padding * 2 - gap * (cols - 1)) / cols;
    const slotH = (height - padding * 2 - gap * (rows - 1)) / rows;

    return Array.from({ length: count }, (_, i) => ({
        x: padding + (i % cols) * (slotW + gap),
        y: padding + Math.floor(i / cols) * (slotH + gap),
        width: slotW,
        height: slotH,
    }));
}

export function loadImage(src: string): Promise<HTMLImageElement> {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => resolve(img);
        img.onerror = () => reject(new Error(`Failed: ${src}`));
        img.src = src;
    });
}

function isNearWhite(r: number, g: number, b: number, a: number) {
    if (a < 10) {
        return true;
    }

    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);

    return max > 236 && min > 224 && max - min < 18;
}

export function detectSlotsFromImage(image: HTMLImageElement, expectedCount: number): SlotPosition[] {
    const maxWidth = 320;
    const ratio = Math.min(1, maxWidth / image.naturalWidth);
    const width = Math.max(1, Math.round(image.naturalWidth * ratio));
    const height = Math.max(1, Math.round(image.naturalHeight * ratio));

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const ctx = canvas.getContext('2d');

    if (!ctx) {
        return [];
    }

    ctx.drawImage(image, 0, 0, width, height);
    const { data } = ctx.getImageData(0, 0, width, height);
    const visited = new Uint8Array(width * height);
    const regions: Array<{ left: number; top: number; right: number; bottom: number; area: number }> = [];

    for (let y = 0; y < height; y += 1) {
        for (let x = 0; x < width; x += 1) {
            const index = y * width + x;

            if (visited[index]) {
                continue;
            }

            visited[index] = 1;

            const pixelIndex = index * 4;

            if (!isNearWhite(data[pixelIndex], data[pixelIndex + 1], data[pixelIndex + 2], data[pixelIndex + 3])) {
                continue;
            }

            const queue = [index];
            let area = 0;
            let left = x;
            let right = x;
            let top = y;
            let bottom = y;

            while (queue.length > 0) {
                const current = queue.pop();

                if (current == null) {
                    continue;
                }

                const currentX = current % width;
                const currentY = Math.floor(current / width);
                area += 1;
                left = Math.min(left, currentX);
                right = Math.max(right, currentX);
                top = Math.min(top, currentY);
                bottom = Math.max(bottom, currentY);

                const neighbors = [
                    current - 1,
                    current + 1,
                    current - width,
                    current + width,
                ];

                neighbors.forEach((neighbor) => {
                    if (neighbor < 0 || neighbor >= width * height) {
                        return;
                    }

                    const neighborX = neighbor % width;
                    const neighborY = Math.floor(neighbor / width);

                    if (
                        Math.abs(neighborX - currentX) + Math.abs(neighborY - currentY) !== 1 ||
                        visited[neighbor]
                    ) {
                        return;
                    }

                    visited[neighbor] = 1;
                    const neighborPixel = neighbor * 4;

                    if (
                        isNearWhite(
                            data[neighborPixel],
                            data[neighborPixel + 1],
                            data[neighborPixel + 2],
                            data[neighborPixel + 3],
                        )
                    ) {
                        queue.push(neighbor);
                    }
                });
            }

            const regionWidth = right - left + 1;
            const regionHeight = bottom - top + 1;
            const regionAspect = regionWidth / regionHeight;

            if (
                area < width * height * 0.008 ||
                regionWidth < width * 0.08 ||
                regionHeight < height * 0.06 ||
                regionAspect < 0.3 ||
                regionAspect > 3.5
            ) {
                continue;
            }

            regions.push({ left, top, right, bottom, area });
        }
    }

    const selected = regions
        .sort((a, b) => b.area - a.area)
        .slice(0, expectedCount)
        .sort((a, b) => (a.top === b.top ? a.left - b.left : a.top - b.top));

    const scaleX = image.naturalWidth / width;
    const scaleY = image.naturalHeight / height;

    return selected.map((region) => ({
        x: Math.round(region.left * scaleX),
        y: Math.round(region.top * scaleY),
        width: Math.round((region.right - region.left + 1) * scaleX),
        height: Math.round((region.bottom - region.top + 1) * scaleY),
    }));
}

function normalizeStoredSlots(
    slots: SlotPosition[],
    width: number,
    height: number,
): SlotPosition[] {
    const looksRelative = slots.every((slot) =>
        [slot.x, slot.y, slot.width, slot.height].every((value) => value >= 0 && value <= 1),
    );

    const normalized = looksRelative
        ? slots.map((slot) => ({
              x: Math.round(slot.x * width),
              y: Math.round(slot.y * height),
              width: Math.round(slot.width * width),
              height: Math.round(slot.height * height),
          }))
        : slots.map((slot) => ({
              x: Math.round(slot.x),
              y: Math.round(slot.y),
              width: Math.round(slot.width),
              height: Math.round(slot.height),
          }));

    return normalized
        .filter((slot) => slot.width > 0 && slot.height > 0)
        .sort((left, right) => (left.y === right.y ? left.x - right.x : left.y - right.y));
}

export interface CapturedPhotoLike {
    id: number;
    url: string;
    order: number;
}

export async function composeFinalImage(
    photos: CapturedPhotoLike[],
    template: TemplateSlotSource | null,
): Promise<string> {
    const { width, height, slots, frameAsset } = await resolveTemplateLayout(template, photos.length);
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;

    const ctx = canvas.getContext('2d');

    if (!ctx) {
        throw new Error('Canvas tidak tersedia');
    }

    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, width, height);

    const sortedPhotos = [...photos].sort((a, b) => a.order - b.order);

    for (let index = 0; index < sortedPhotos.length; index += 1) {
        const photo = sortedPhotos[index];
        const slot = slots[index];

        if (!slot) {
            continue;
        }

        try {
            const image = await loadImage(photo.url);
            ctx.save();
            ctx.beginPath();
            ctx.rect(slot.x, slot.y, slot.width, slot.height);
            ctx.clip();

            const scale = Math.max(slot.width / image.width, slot.height / image.height);
            const drawWidth = image.width * scale;
            const drawHeight = image.height * scale;

            ctx.drawImage(
                image,
                slot.x + (slot.width - drawWidth) / 2,
                slot.y + (slot.height - drawHeight) / 2,
                drawWidth,
                drawHeight,
            );
            ctx.restore();
        } catch {
            ctx.fillStyle = '#e5e7eb';
            ctx.fillRect(slot.x, slot.y, slot.width, slot.height);
        }
    }

    if (frameAsset) {
        ctx.drawImage(frameAsset, 0, 0, width, height);
    }

    return canvas.toDataURL('image/jpeg', 0.95);
}

export async function resolveTemplateLayout(
    template: TemplateSlotSource | null,
    photoCount: number,
): Promise<{
    width: number;
    height: number;
    slots: SlotPosition[];
    frameAsset: HTMLImageElement | null;
}> {
    const printSize = template?.print_size ?? 'a4';
    let frameAsset: HTMLImageElement | null = null;

    if (template?.frame_path) {
        try {
            frameAsset = await loadImage(template.frame_path);
        } catch {
            frameAsset = null;
        }
    }

    if (!frameAsset && template?.thumbnail_path) {
        try {
            frameAsset = await loadImage(template.thumbnail_path);
        } catch {
            frameAsset = null;
        }
    }

    const fallback = canvasDimensions(printSize);
    const width = frameAsset?.naturalWidth || frameAsset?.width || fallback.W;
    const height = frameAsset?.naturalHeight || frameAsset?.height || fallback.H;

    const rawSlots = template?.slot_positions;
    const hasValidRawSlots =
        rawSlots?.length &&
        rawSlots.every((slot) => slot.x != null && slot.y != null && slot.width != null && slot.height != null);
    const normalizedRawSlots = hasValidRawSlots ? normalizeStoredSlots(rawSlots!, width, height) : [];
    const hasStoredSlots = normalizedRawSlots.length > 0;
    const hasEnoughRawSlots = normalizedRawSlots.length >= photoCount;

    const expectedCount = Math.max(photoCount, template?.photo_slots ?? 0);
    const detectedSlots =
        !hasStoredSlots && frameAsset
            ? detectSlotsFromImage(frameAsset, expectedCount)
            : [];
    const hasEnoughDetectedSlots = detectedSlots.length >= photoCount;

    const slots = hasEnoughRawSlots
        ? normalizedRawSlots.slice(0, photoCount)
        : hasEnoughDetectedSlots
          ? detectedSlots.slice(0, photoCount)
          : generateDefaultSlots(photoCount, width, height, printSize);

    return { width, height, slots, frameAsset };
}
