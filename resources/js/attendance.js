/**
 * Modul Presensi Mandiri Kamera Webcam & Geolocation
 */
document.addEventListener('DOMContentLoaded', () => {
    const attendanceApp = document.getElementById('attendance-camera-app');
    if (!attendanceApp) return;

    const video = document.getElementById('webcam-video');
    const canvas = document.getElementById('webcam-canvas');
    const captureBtn = document.getElementById('btn-capture-selfie');
    const retakeBtn = document.getElementById('btn-retake-selfie');
    const selfieInput = document.getElementById('selfie-input');
    const selfiePreview = document.getElementById('selfie-preview');
    const cameraPlaceholder = document.getElementById('camera-placeholder');

    const latInput = document.getElementById('latitude-input');
    const lonInput = document.getElementById('longitude-input');
    const geoStatus = document.getElementById('geo-status-badge');
    const geoDistanceText = document.getElementById('geo-distance-text');
    const submitBtn = document.getElementById('btn-submit-attendance');

    const branchLat = parseFloat(attendanceApp.dataset.branchLat);
    const branchLon = parseFloat(attendanceApp.dataset.branchLon);
    const branchRadius = parseFloat(attendanceApp.dataset.branchRadius) || 100;
    const workTypeSelect = document.getElementById('work_type_select');

    let stream = null;

    // 1. Inisialisasi Kamera Depan
    async function initCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false
            });
            if (video) {
                video.srcObject = stream;
                video.classList.remove('hidden');
                if (cameraPlaceholder) cameraPlaceholder.classList.add('hidden');
            }
        } catch (err) {
            console.error('Kamera gagal diakses:', err);
            if (cameraPlaceholder) {
                cameraPlaceholder.innerHTML = '<span class="text-rose-500 text-xs">Izin kamera ditolak atau tidak tersedia. Pastikan izin kamera aktif.</span>';
            }
        }
    }

    initCamera();

    // 2. Ambil Snapshot Selfie
    if (captureBtn && video && canvas) {
        captureBtn.addEventListener('click', () => {
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
            if (selfieInput) selfieInput.value = dataUrl;
            if (selfiePreview) {
                selfiePreview.src = dataUrl;
                selfiePreview.classList.remove('hidden');
            }
            video.classList.add('hidden');
            captureBtn.classList.add('hidden');
            if (retakeBtn) retakeBtn.classList.remove('hidden');

            checkReadyToSubmit();
        });
    }

    // 3. Ulangi Foto (Retake)
    if (retakeBtn) {
        retakeBtn.addEventListener('click', () => {
            if (selfieInput) selfieInput.value = '';
            if (selfiePreview) selfiePreview.classList.add('hidden');
            if (video) video.classList.remove('hidden');
            retakeBtn.classList.add('hidden');
            if (captureBtn) captureBtn.classList.remove('hidden');

            checkReadyToSubmit();
        });
    }

    // 4. Deteksi Geolocation GPS
    function updateLocation() {
        if (!navigator.geolocation) {
            if (geoStatus) geoStatus.textContent = 'GPS tidak didukung oleh browser';
            return;
        }

        if (geoStatus) geoStatus.textContent = 'Mencari titik koordinat GPS...';

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lon = pos.coords.longitude;

                if (latInput) latInput.value = lat;
                if (lonInput) lonInput.value = lon;

                if (geoStatus) {
                    geoStatus.textContent = `GPS Terkunci (${lat.toFixed(5)}, ${lon.toFixed(5)})`;
                    geoStatus.className = 'px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
                }

                // Kalkulasi Jarak Geofencing
                if (!isNaN(branchLat) && !isNaN(branchLon) && geoDistanceText) {
                    const dist = calculateHaversine(lat, lon, branchLat, branchLon);
                    const isInside = dist <= branchRadius;
                    const isWfo = !workTypeSelect || workTypeSelect.value === 'WFO';

                    if (isWfo) {
                        geoDistanceText.textContent = `Jarak ke kantor: ${Math.round(dist)} m (Batas radius: ${branchRadius} m)`;
                        geoDistanceText.className = isInside ? 'text-xs text-emerald-600 font-medium' : 'text-xs text-rose-600 font-semibold';
                    } else {
                        geoDistanceText.textContent = 'Mode WFH: Bebas radius lokasi kantor.';
                        geoDistanceText.className = 'text-xs text-slate-500 font-medium';
                    }
                }

                checkReadyToSubmit();
            },
            (err) => {
                if (geoStatus) {
                    geoStatus.textContent = 'Izin lokasi GPS ditolak/tidak aktif';
                    geoStatus.className = 'px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200';
                }
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }

    updateLocation();

    if (workTypeSelect) {
        workTypeSelect.addEventListener('change', updateLocation);
    }

    // Formula Haversine di Javascript
    function calculateHaversine(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const toRad = (x) => (x * Math.PI) / 180;
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function checkReadyToSubmit() {
        const hasSelfie = selfieInput && selfieInput.value.length > 0;
        const hasLocation = latInput && latInput.value.length > 0;
        if (submitBtn) {
            submitBtn.disabled = !(hasSelfie && hasLocation);
        }
    }
});
