<script>
/**
 * initZone(zoneId, inputId, prevWrapperId, prevImgId, clearId, phId, isThumbnail)
 * isThumbnail = true → clicking "clear" also sets #remove-thumbnail = 1
 */
function initZone(zoneId, inputId, prevWrapperId, prevImgId, clearId, phId, isThumbnail) {
    var zone  = document.getElementById(zoneId);
    var input = document.getElementById(inputId);
    var prevW = document.getElementById(prevWrapperId);
    var prevI = document.getElementById(prevImgId);
    var clear = document.getElementById(clearId);
    var ph    = document.getElementById(phId);

    if (!zone) return;

    function showPreview(file) {
        if (!file || !file.type.startsWith('image/')) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            prevI.src = e.target.result;
            prevW.style.display = 'flex';
            ph.style.display    = 'none';
        };
        reader.readAsDataURL(file);
    }

    input.addEventListener('change', function () {
        if (this.files[0]) showPreview(this.files[0]);
    });

    if (clear) {
        clear.addEventListener('click', function (e) {
            e.stopPropagation();
            input.value = '';
            prevI.src   = '';
            prevW.style.display = 'none';
            ph.style.display    = 'block';
            if (isThumbnail) {
                var removeFld = document.getElementById('remove-thumbnail');
                if (removeFld) removeFld.value = '1';
            }
        });
    }

    zone.addEventListener('dragover',  function (e) { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', function ()  { zone.classList.remove('dragover'); });
    zone.addEventListener('drop', function (e) {
        e.preventDefault(); zone.classList.remove('dragover');
        var f = e.dataTransfer.files[0];
        if (f) { input.files = e.dataTransfer.files; showPreview(f); }
    });
}
</script>
