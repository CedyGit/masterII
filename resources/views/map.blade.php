<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Infrastructures Publiques Madagascar</title>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

            #header {
            position: relative;         /* nécessaire pour positionner le bouton absolu */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;   /* centre horizontalement le header-content */
            text-align: center;
            min-height: 72px;
        }


        #header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        #header p {
            opacity: 0.9;
            font-size: 14px;
        }

        #container {
            display: flex;
            height: calc(100vh - 100px);
             transition: margin-left 0.3s ease;
        }

        #sidebar {
            width: 320px;
            background: white;
            padding: 20px;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
        }

        #map {
            flex: 1;
            height: 100%;
        }

        .control-group {
            margin-bottom: 20px;
        }

        .control-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }

        .control-group select,
        .control-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .control-group select:focus,
        .control-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        #stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        #stats h3 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: #666;
        }

        .stat-value {
            font-weight: 600;
            color: #667eea;
        }

        .loader {
            text-align: center;
            padding: 20px;
            color: #667eea;
        }

        .error {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        /* Leaflet popup styling */
        .leaflet-popup-content {
            margin: 15px;
            min-width: 200px;
        }

        .popup-title {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
        }

        .popup-info {
            font-size: 13px;
            line-height: 1.6;
            color: #666;
        }

        .popup-info strong {
            color: #333;
        }

       /* legende */
       .legend {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            min-width: 150px;
            line-height: 1.5;
        }

        .legend h4 {
            margin: 0 0 10px;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
            font-size: 13px;
            color: #555;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
            display: inline-block;
            border: 1px solid rgba(0,0,0,0.1);
        }


        #toggle-sidebar {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(0,0,0,0.2);
            color: white;
            border: none;
            padding: 8px 12px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.2s;
        }

        #toggle-sidebar:hover {
            background: rgba(0,0,0,0.4);
        }


        .header-content {
            display: inline-block;
        }

        #header h1 {
            font-size: 20px;
            margin-bottom: 4px;
        }
        #header p {
            margin: 0;
            opacity: 0.95;
            font-size: 13px;
        }

        #toggle-sidebar {
            position: absolute;
            left: 16px;             
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.12);
            color: white;
            border: none;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            z-index: 2200;
            font-size: 18px;
            transition: background 0.15s, transform 0.15s;
        }
        #toggle-sidebar:hover {
            background: rgba(255,255,255,0.22);
            transform: translateY(-50%) scale(1.03);
        }


        #sidebar {
        width: 320px;
        transition: width 0.28s ease, padding 0.28s ease;
        }

        #sidebar.collapsed {
        width: 0 !important;
        padding: 0 !important;
        overflow: hidden;
        }


        #container {
        display: flex;
        height: calc(100vh - 100px);
        transition: all 0.28s ease;
        }

        #container.fullscreen #map,
        body.sidebar-collapsed #map {
        width: 100%;
        flex: 1 1 auto;
        transition: width 0.28s ease;
        }

        #map {
        min-width: 0;
        }

        #toggle-sidebar[aria-expanded="false"] {
        transform: translateY(-50%) rotate(180deg);
        }
    </style>
</head>
<body>
    
    <div id="header">
        <button id="toggle-sidebar" aria-expanded="true" title="Cacher / afficher le menu">☰</button>

        <div class="header-content">
            <h1>🗺️ API Infrastructures Publiques de Madagascar</h1>
            <p>Visualisation interactive basée sur OpenStreetMap</p>
        </div>
    </div>

    <div id="container">
        <div id="sidebar">
    <div id="stats">
        <h3>📊 Statistiques</h3>
        <div id="stats-content">
            <div class="loader">Chargement...</div>
        </div>
    </div>

    <div class="control-group">
        <label for="type-filter">🏛️ Filtrer par type</label>
        <select id="type-filter">
            <option value="">Tous les types</option>
        </select>
    </div>

    <div class="control-group" style="margin-top: 20px;">
        <label>🎯 Recherche à proximité</label>
        <input type="number" id="radius" placeholder="Rayon (km)" value="10" step="1" min="1" max="100">
        <button onclick="searchNearby()" style="margin-top: 10px;">📍 Chercher autour de moi</button>
    </div>
</div>

        <div id="map"></div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // Initialisation de la carte centrée sur Madagascar
        const map = L.map('map').setView([-18.8792, 47.5079], 6);

        // Ajout du fond de carte OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        // Groupe de markers pour pouvoir les effacer facilement
        let markersLayer = L.layerGroup().addTo(map);

        // Couleurs par type d'infrastructure
        const typeColors = {
            'school': '#3498db',
            'hospital': '#e74c3c',
            'clinic': '#f39c12',
            'police': '#9b59b6',
            'townhall': '#1abc9c',
            'government': '#34495e',
            'university': '#e67e22'
        };

        const typeIcons = {
            'school': '🏫',
            'hospital': '🏥',
            'clinic': '⚕️',
            'police': '👮',
            'townhall': '🏛️',
            'government': '🏢',
            'university': '🎓'
        };

                // Création du contrôle Leaflet pour la légende
            const legend = L.control({ position: 'bottomright' });

            legend.onAdd = function (map) {
                const div = L.DomUtil.create('div', 'legend');
                div.innerHTML = '<h4>Légende</h4>';

                // Génération dynamique basée sur votre objet typeColors
                for (const [type, color] of Object.entries(typeColors)) {
                    const icon = typeIcons[type] || '';
                    div.innerHTML += `
                        <div class="legend-item">
                            <span class="legend-color" style="background:${color}"></span>
                            <span>${icon} ${type}</span>
                        </div>
                    `;
                }
                return div;
            };

            legend.addTo(map);

        // Charger les statistiques
        async function loadStats() {
            try {
                const response = await fetch('/api/v1/stats');
                const data = await response.json();
                
                let html = `<div class="stat-item">
                    <span class="stat-label">Total</span>
                    <span class="stat-value">${data.total}</span>
                </div>`;

                data.by_type.forEach(stat => {
                    html += `<div class="stat-item">
                        <span class="stat-label">${typeIcons[stat.type] || '📍'} ${stat.type}</span>
                        <span class="stat-value">${stat.count}</span>
                    </div>`;
                });

                document.getElementById('stats-content').innerHTML = html;
            } catch (error) {
                document.getElementById('stats-content').innerHTML = 
                    '<div class="error">Erreur de chargement des stats</div>';
            }
        }

        // Charger les types pour le filtre
        async function loadTypes() {
            try {
                const response = await fetch('/api/v1/types');
                const data = await response.json();
                
                const select = document.getElementById('type-filter');
                data.data.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.name;
                    option.textContent = `${typeIcons[type.name] || '📍'} ${type.name}`;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Erreur chargement types:', error);
            }
        }

        // Charger les infrastructures
     // Charger les infrastructures
        async function loadInfrastructures() {
            const type = document.getElementById('type-filter').value;
            
            let url = '/api/v1/infrastructures';
            if (type) {
                url += `?type=${type}`;
            }

            console.log('🔍 Chargement depuis:', url);

            try {
                const response = await fetch(url);
                const data = await response.json();
                
                console.log('📦 Données reçues:', data.count, 'infrastructures');

                // Effacer les anciens markers
                markersLayer.clearLayers();

                let loaded = 0;
                let errors = 0;

                // Ajouter les nouveaux markers
                data.features.forEach(feature => {
                    try {
                        const geom = feature.geometry;
                        const props = feature.properties;
                        
                        let latLng;

                        // Gérer différents types de géométrie
                        if (geom.type === 'Point') {
                            // Point simple: [lng, lat]
                            latLng = [geom.coordinates[1], geom.coordinates[0]];
                        } else if (geom.type === 'Polygon') {
                            // Polygon: prendre le centroid (premier point du premier anneau)
                            const firstPoint = geom.coordinates[0][0];
                            latLng = [firstPoint[1], firstPoint[0]];
                        } else if (geom.type === 'MultiPolygon') {
                            // MultiPolygon: prendre le premier point du premier polygone
                            const firstPoint = geom.coordinates[0][0][0];
                            latLng = [firstPoint[1], firstPoint[0]];
                        } else {
                            console.warn('Type de géométrie non supporté:', geom.type);
                            errors++;
                            return;
                        }

                        const color = typeColors[props.type] || '#95a5a6';
                        
                        const marker = L.circleMarker(latLng, {
                            radius: 8,
                            fillColor: color,
                            color: '#fff',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.8
                        });

                        const popupContent = `
                            <div class="popup-title">${typeIcons[props.type] || '📍'} ${props.name}</div>
                            <div class="popup-info">
                                <strong>Type:</strong> ${props.type}<br>
                                ${props.level ? `<strong>Niveau:</strong> ${props.level}<br>` : ''}
                                ${props.operator ? `<strong>Opérateur:</strong> ${props.operator}<br>` : ''}
                                <strong>Géométrie:</strong> ${geom.type}
                            </div>
                        `;

                        marker.bindPopup(popupContent);
                        marker.addTo(markersLayer);
                        loaded++;
                    } catch (e) {
                        errors++;
                        console.error('Erreur sur une feature:', e);
                    }
                });

                console.log(`✅ ${loaded} infrastructures chargées, ${errors} erreurs`);
                
                if (loaded === 0) {
                    alert('Aucune infrastructure trouvée avec ces filtres');
                }
            } catch (error) {
                console.error('❌ Erreur complète:', error);
                alert('Erreur de chargement des données: ' + error.message);
            }
        }

        // Recherche à proximité
        function searchNearby() {
            if (!navigator.geolocation) {
                alert('Géolocalisation non supportée par votre navigateur');
                return;
            }

            navigator.geolocation.getCurrentPosition(async (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const radius = document.getElementById('radius').value || 10;

                try {
                    const response = await fetch(`/api/v1/infrastructures/nearby?lat=${lat}&lng=${lng}&radius=${radius}`);
                    const data = await response.json();

                    markersLayer.clearLayers();

                    // Ajouter un marker pour votre position
                    L.marker([lat, lng], {
                        icon: L.divIcon({
                            className: 'user-location',
                            html: '📍',
                            iconSize: [30, 30]
                        })
                    }).addTo(markersLayer).bindPopup('Votre position');

                    // Ajouter les infrastructures trouvées
                    data.features.forEach(feature => {
                        const geom = feature.geometry;
                        const props = feature.properties;
                        
                        let latLng;
                        
                        // Gérer différents types de géométrie
                        if (geom.type === 'Point') {
                            latLng = [geom.coordinates[1], geom.coordinates[0]];
                        } else if (geom.type === 'Polygon') {
                            const firstPoint = geom.coordinates[0][0];
                            latLng = [firstPoint[1], firstPoint[0]];
                        } else if (geom.type === 'MultiPolygon') {
                            const firstPoint = geom.coordinates[0][0][0];
                            latLng = [firstPoint[1], firstPoint[0]];
                        } else {
                            return;
                        }
                        
                        const color = typeColors[props.type] || '#95a5a6';

                        const marker = L.circleMarker(latLng, {
                            radius: 8,
                            fillColor: color,
                            color: '#fff',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.8
                        });

                        const popupContent = `
                            <div class="popup-title">${typeIcons[props.type] || '📍'} ${props.name}</div>
                            <div class="popup-info">
                                <strong>Distance:</strong> ${props.distance_km} km<br>
                                <strong>Type:</strong> ${props.type}
                            </div>
                        `;

                        marker.bindPopup(popupContent);
                        marker.addTo(markersLayer);
                    });

                    // Centrer la carte
                    map.setView([lat, lng], 12);
                    
                    alert(`✅ ${data.count} infrastructure(s) trouvée(s) dans un rayon de ${radius} km`);
                } catch (error) {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la recherche');
                }
            }, (error) => {
                alert('Impossible d\'obtenir votre position');
            });
        }

        // Initialisation au chargement de la page
        window.onload = () => {
            loadStats();
            loadTypes();
            loadInfrastructures();

            document.getElementById('type-filter').addEventListener('change', function() {
                loadInfrastructures();
            });
        };
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleButton = document.getElementById('toggle-sidebar');
            const sidebar = document.getElementById('sidebar');
            const container = document.getElementById('container');

            if (!toggleButton || !sidebar || !container) {
            console.error('Toggle: éléments manquants', { toggleButton, sidebar, container });
            return;
            }

            // état initial (au cas où)
            const initiallyCollapsed = sidebar.classList.contains('collapsed');
            toggleButton.setAttribute('aria-expanded', (!initiallyCollapsed).toString());
            if (initiallyCollapsed) document.body.classList.add('sidebar-collapsed');

            toggleButton.addEventListener('click', () => {
            const nowCollapsed = sidebar.classList.toggle('collapsed');

            // on marque le container pour CSS si besoin
            container.classList.toggle('fullscreen', nowCollapsed);
            document.body.classList.toggle('sidebar-collapsed', nowCollapsed);

            // accessibilité
            toggleButton.setAttribute('aria-expanded', (!nowCollapsed).toString());

            // redraw Leaflet map après transition (timeout légèrement supérieur à la transition CSS)
            setTimeout(() => {
                try {
                if (typeof map !== 'undefined' && map && typeof map.invalidateSize === 'function') {
                    map.invalidateSize();
                }
                } catch (e) {
                console.warn('map.invalidateSize() failed:', e);
                }
            }, 320);
            });
        });
    </script>

</body>
</html>