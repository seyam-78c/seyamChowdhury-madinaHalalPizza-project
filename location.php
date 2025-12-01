<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distance Calculation</title>
</head>

<body>
    <div class="container">
        <h2>Calculate Distance to Restaurant</h2>
        <label for="autocomplete">Enter Address or Use "Locate Me":</label>
        <input id="autocomplete" type="text" class="form-control" placeholder="Type your address">
        <button onclick="locateMe()">Locate Me</button>
        <p id="userLocation"></p>
        <button onclick="calculateDistance()">Calculate Distance</button>
        <p id="distance"></p>
    </div>

    <script>
    let autocomplete;
    const restaurantLocation = {
        lat: 43.68096692011496,
        lng: -79.33536456442678
    }; // Replace with your restaurant's location
    let userLocation = null;

    function initAutocomplete() {
        autocomplete = new google.maps.places.Autocomplete(
            document.getElementById('autocomplete'), {
                types: ['geocode']
            }
        );

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            if (place.geometry && place.geometry.location) {
                userLocation = {
                    lat: place.geometry.location.lat(),
                    lng: place.geometry.location.lng()
                };
                document.getElementById('userLocation').innerText =
                    `Selected Location: (${userLocation.lat}, ${userLocation.lng})`;
            }
        });
    }

    function locateMe() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    document.getElementById('userLocation').innerText =
                        `Current Location: (${userLocation.lat}, ${userLocation.lng})`;
                    document.getElementById('autocomplete').value = '';
                },
                (error) => {
                    alert("Unable to retrieve your location. Please type it manually.");
                }
            );
        } else {
            alert("Geolocation is not supported by your browser.");
        }
    }

    function calculateDistance() {
        if (!userLocation) {
            alert("Please provide your location.");
            return;
        }

        const userLatLng = new google.maps.LatLng(userLocation.lat, userLocation.lng);
        const restaurantLatLng = new google.maps.LatLng(restaurantLocation.lat, restaurantLocation.lng);

        const distance = google.maps.geometry.spherical.computeDistanceBetween(userLatLng, restaurantLatLng);
        const distanceInKm = (distance / 1000).toFixed(2); // Convert to kilometers
        document.getElementById('distance').innerText = `Distance to Restaurant: ${distanceInKm} km`;
    }
    </script>
    <script async
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCbFtWxrgq5P_RQ6X_Rinpnk1OnRyrXKWY&libraries=places,geometry&callback=initAutocomplete">
    </script>
</body>

</html>