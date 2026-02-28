# Aurora-Dashboard

A lightweight web app that displays current aurora activity and nightly forecasts.  
Uses a PHP API to fetch data from NOAA’s OVATION aurora service and shows it on a simple HTML/JS frontend.

---

## Features

- Detects user location (optional) to show **local aurora intensity and probability**.  
- Displays **global aurora activity** for both Northern and Southern Hemispheres.  
- Shows **nightly static aurora forecast images**.  
- Lightweight and self-contained: only **PHP, HTML, and JS** (no database required).  
- Simple caching to reduce repeated requests to the NOAA API.

---

## Setup / Usage

1. Clone the repository:

```bash
git clone https://github.com/your-username/aurora-forecast.git
cd aurora-forecast
