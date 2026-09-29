/**
 * Modul Peta Interaktif Geofencing Cabang NexusHRIS
 * Menggunakan Leaflet.js & OpenStreetMap via CDN dinamis.
 */
document.addEventListener('DOMContentLoaded', () => {
    const mapContainer = document.getElementById('branch-map');
    if (!mapContainer) return;

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const radiusInput = document.getElementById('radius_meters');
    const locateBtn = document.getElementById('btn-get-location');
    const isInteractive = mapContainer.dataset.interactive === 'true';

    // Koordinat awal (default Jakarta jika kosong)
    const initialLat = parseFloat(latInput?.value) || parseFloat(mapContainer.dataset.lat) || -6.2088;
    const initialLng = parseFloat(lngInput?.value) || parseFloat(mapContainer.dataset.lng) || 106.8456;
    const initialRadius = parseInt(radiusInput?.value) || parseInt(mapContainer.dataset.radius) || 50;

    // Load Leaflet CSS dinamis jika belum ada
    if (!document.getElementById('leaflet-css')) {
        const link = document.createElement('link');
        link.id = 'leaflet-css';
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);
    }

    // Fungsi inisialisasi setelah script Leaflet siap
    function initLeaflet() {
        if (typeof L === 'undefined') return;

        const map = L.map('branch-map').setView([initialLat, initialLng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let marker = L.marker([initialLat, initialLng], {
            draggable: isInteractive
        }).addTo(map);

        let circle = L.circle([initialLat, initialLng], {
            color: '#0284c7',       // sky-600
            fillColor: '#38bdf8',   // sky-400
            fillOpacity: 0.25,
            radius: initialRadius,
            weight: 2
        }).addTo(map);

        // Update nilai koordinat di input form
        function updateInputs(lat, lng) {
            if (latInput) latInput.value = lat.toFixed(7);
            if (lngInput) lngInput.value = lng.toFixed(7);
        }

        // Sinkronisasi posisi marker & lingkaran
        function setPosition(lat, lng) {
            marker.setLatLng([lat, lng]);
            circle.setLatLng([lat, lng]);
            map.panTo([lat, lng]);
            updateInputs(lat, lng);
        }

        if (isInteractive) {
            // Klik pada peta untuk memindahkan pin
            map.on('click', (e) => {
                setPosition(e.latlng.lat, e.latlng.lng);
            });

            // Geser marker secara langsung
            marker.on('dragend', () => {
                const pos = marker.getLatLng();
                setPosition(pos.lat, pos.lng);
            });

            // Perubahan manual pada input latitude/longitude
            const handleCoordChange = () => {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    setPosition(lat, lng);
                }
            };

            latInput?.addEventListener('change', handleCoordChange);
            lngInput?.addEventListener('change', handleCoordChange);

            // Perubahan dinamis pada input radius
            radiusInput?.addEventListener('input', () => {
                const rad = parseInt(radiusInput.value) || 50;
                circle.setRadius(rad);
            });

            // Tombol "Gunakan Lokasi Saat Ini" via GPS Browser
            if (locateBtn && navigator.geolocation) {
                locateBtn.addEventListener('click', () => {
                    locateBtn.disabled = true;
                    locateBtn.classList.add('opacity-50');
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            setPosition(lat, lng);
                            locateBtn.disabled = false;
                            locateBtn.classList.remove('opacity-50');
                        },
                        (error) => {
                            alert('Gagal mengambil koordinat lokasi: ' + error.message);
                            locateBtn.disabled = false;
                            locateBtn.classList.remove('opacity-50');
                        },
                        { enableHighAccuracy: true }
                    );
                });
            }
        }
    }

    // Load Leaflet JS jika belum ada
    if (typeof L === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = initLeaflet;
        document.head.appendChild(script);
    } else {
        initLeaflet();
    }
});
