<!DOCTYPE html>
<html>
<head>
    <title>Aurora Forecast</title>
    <style>
        *, *::before, *::after {
          box-sizing: border-box;
        }
        html, body {
            width:100%;
            padding:0;
            margin:0;
            background-color:#000000;
            padding:0.75rem 0.5rem;
        }
        h1, h2, h3, h4, h5, h6, p, div {
            color:white;
            font-family: sans-serif;
        }
        .spacer {
             height:2rem;
        }
        p.main-header {
            background-color:#313b45;
            margin:0;
            margin-bottom:1.5rem;
            padding:0.5rem 0rem;
            font-weight: bold;
            text-align: center;
            border-radius:1rem;
            border: solid 1.0px #5b6e80;
            font-size: 1.5rem;
        }
        p.secondary-header {
            text-align: center;
            font-size: 1.9rem;
        }
        p.tertiary-header {
            font-size:1.7rem;
        }
        p.local-forecast-data {
            font-size:1.9rem;
        }
        .local-forecast {
            position:relative;
            display:block;
            padding:0;
            margin:0;
        }
        .local-forecast-wrapper {
            display:flex;
            flex-direction:row;
            flex-wrap:wrap;
            align-items:center;
            justify-content: space-around;
        }
        .local-forecast-item p {
            display:inline-block;
            margin: 0.5rem;
        }
        #location-notice {
            text-align:center;
            color:red;
            font-size:1.2rem;
        }
        .aurora-forecast {
            width:100%;
            position:relative;
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            padding:0;
            margin:0;
        }
        .aurora-forecast-section {
            padding-bottom:1.5rem;
        }
        .realtime-forecast, .overall-forecast {
            width:100%;
            position:relative;
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            padding:0;
            margin:0;
        }
        .realtime-forecast div, .overall-forecast div {
            position:relative;
            display: flex;
            flex-direction: column;
            margin:0;
            padding:0;
        }
        .aurora-forecast-image {
            display:block;
            max-width:100%;
        }
        /* Narrower Screens */
        @media (max-aspect-ratio: 3/4) {
          .aurora-forecast {
            grid-template-columns: 1fr;
          }
          p.main-header {
              font-size: 2rem;
          }
          p.secondary-header {
              font-size: 2.2rem;
          }
        }
    </style>
</head>
<body>

<div class="local-forecast">
    <header>
        <p class="main-header">Your Location</p>
    </header>
    <div class="local-forecast-wrapper">
        <div class="local-forecast-item">
            <p class="tertiary-header">Aurora Presence:</p>
            <p id="aurora-presence" class="local-forecast-data">-</p>
        </div>
        <div class="local-forecast-item">
            <p class="tertiary-header">Intensity:</p>
            <p id="aurora-intensity" class="local-forecast-data">-</p>
        </div>
        <div class="local-forecast-item">
            <p class="tertiary-header">Probability:</p>
            <p id="aurora-probability" class="local-forecast-data">-</p>
        </div>
    </div>
</div>
<div class="spacer"></div>
<p id="location-notice" style="display:none;">You can allow access to your location to get a local forecast.</p>
<div class="aurora-forecast">
    <div class="aurora-forecast-section">
        <header>
            <p class="main-header">Predicted Activity (<span id="forecast-lead-time">-</span> minutes from now)</p>
        </header>
        <div class="realtime-forecast">
            <div>
                <p class="secondary-header">Northern Hemisphere</p>
                <img class="aurora-forecast-image" src="https://services.swpc.noaa.gov/images/animations/ovation/north/latest.jpg" alt="Northern Hemisphere aurora forecast" />
            </div>
            <div>
                <p class="secondary-header">Southern Hemisphere</p>
                <img class="aurora-forecast-image" src="https://services.swpc.noaa.gov/images/animations/ovation/south/latest.jpg" alt="Southern Hemisphere aurora forecast" />
            </div>
        </div>
    </div>
    <div class="aurora-forecast-section">
        <header>
            <p class="main-header">Nightly Forecast</p>
        </header>
        <div class="overall-forecast">
            <div>
                <p class="secondary-header">Tonight</p>
                <img class="aurora-forecast-image" src="https://services.swpc.noaa.gov/experimental/images/aurora_dashboard/tonights_static_viewline_forecast.png" alt="Tonight's aurora forecast" />
            </div>
            <div>
                <p class="secondary-header">Tomorrow Night</p>
                <img class="aurora-forecast-image" src="https://services.swpc.noaa.gov/experimental/images/aurora_dashboard/tomorrow_nights_static_viewline_forecast.png" alt="Tomorrow night's aurora forecast" />
            </div>
        </div>
    </div>
</div>

<script>

function getAuroraData() {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;
        fetch(`api.php?lat=${lat}&lon=${lon}`)
          .then(res => res.json())
          .then(data => {
            if ('lead_time_minutes' in data) {document.getElementById("forecast-lead-time").innerHTML = '~'+data.lead_time_minutes;}
            if ('category' in data) {document.getElementById("aurora-presence").innerHTML = data.category;}
            if ('intensity' in data && !isNaN(data.intensity)) {document.getElementById("aurora-intensity").innerHTML = data.intensity;}
            if ('probability' in data && !isNaN(data.probability)) {document.getElementById("aurora-probability").innerHTML = '~'+data.probability+'%';}
        });
      },
      (err) => {
        // console.error('Could not get location', err);
        document.getElementById("location-notice").style.display = "block";
        fetch(`api.php`)
          .then(res => res.json())
          .then(data => {
          if ('lead_time_minutes' in data) {document.getElementById("forecast-lead-time").innerHTML = data.lead_time_minutes;}
        });
      }
    );
    
}
getAuroraData();
</script>



</body>
</html>