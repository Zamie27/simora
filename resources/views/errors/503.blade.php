@php
// Pengaturan Waktu Maintenance (Opsional)
$estimasi_selesai = "30 April 2026 08:00"; 
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMORA | Maintenance Mode</title>
    
    <!-- SEO & Icons -->
    <meta name="description" content="Sistem Informasi Monitoring Atlet Sepeda - SIMORA. Saat ini kami sedang melakukan pembaharuan infrastruktur.">
    <link rel="icon" type="image/png" href="/images/simora_icon.png">
    <link rel="apple-touch-icon" href="/images/simora_icon.png">
    
    <!-- Open Graph -->
    <meta property="og:title" content="SIMORA | Maintenance Mode">
    <meta property="og:description" content="Monitoring atlet sepeda dengan akurasi laboratorium.">
    <meta property="og:image" content="/images/simora_icon.png">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #050A1A;
            --accent-color: #FF7A50;
            --text-color: #FFFFFF;
            --subtext-color: #AAAAAA;
            --card-bg: rgba(255, 255, 255, 0.03);
            --gradient-primary: linear-gradient(135deg, #FF7A50 0%, #FF6120 100%);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Ambient Background Effect */
        body::before {
            content: "";
            position: absolute;
            top: -10%;
            right: -10%;
            width: 50%;
            height: 50%;
            background: radial-gradient(circle, rgba(255, 122, 80, 0.05) 0%, rgba(5, 10, 26, 0) 70%);
            z-index: 0;
            pointer-events: none;
        }

        /* Layout Grid 2 Kolom */
        .wrapper {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* SISI KIRI: Branding & Visual */
        .branding-side {
            padding: 80px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: 1px solid rgba(255,255,255,0.05);
            background: linear-gradient(to right, rgba(5, 10, 26, 1), rgba(255, 255, 255, 0.02));
        }

        .logo-container img {
            max-width: 200px;
            height: auto;
            opacity: 0.9;
            transition: opacity 0.3s ease;
        }
        
        .logo-container img:hover {
            opacity: 1;
        }

        .hero-text {
            margin-top: 40px;
        }

        .hero-text h2 {
            font-size: 14px;
            color: var(--accent-color);
            letter-spacing: 6px;
            margin-bottom: 20px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .hero-text h1 {
            font-family: 'Oswald', sans-serif;
            font-size: clamp(48px, 6vw, 84px);
            line-height: 1;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 40px;
            letter-spacing: -2px;
        }

        .hero-text span {
            color: var(--accent-color);
            font-style: italic;
            display: block;
            margin-top: 5px;
        }

        .hero-description {
            color: var(--subtext-color);
            max-width: 440px;
            font-size: 18px;
            line-height: 1.7;
            font-weight: 300;
        }

        /* SISI KANAN: Form/Status */
        .content-side {
            padding: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.2);
        }

        .status-box {
            max-width: 480px;
        }

        .status-box h3 {
            font-family: 'Oswald', sans-serif;
            font-size: 38px;
            margin-bottom: 20px;
            font-style: italic;
            text-transform: uppercase;
        }

        .status-box p {
            color: var(--subtext-color);
            margin-bottom: 48px;
            font-size: 17px;
            line-height: 1.8;
            font-weight: 300;
        }

        .info-card {
            background: var(--card-bg);
            border-left: 4px solid var(--accent-color);
            padding: 30px;
            margin-bottom: 48px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, background 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateX(10px);
            background: rgba(255, 255, 255, 0.05);
        }

        .info-card label {
            font-size: 13px;
            color: var(--accent-color);
            text-transform: uppercase;
            letter-spacing: 3px;
            display: block;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .info-card .value {
            font-size: 32px;
            font-weight: 700;
            font-family: 'Oswald', sans-serif;
            letter-spacing: 1px;
        }

        .btn-contact {
            display: inline-flex;
            align-items: center;
            background: var(--gradient-primary);
            color: white;
            padding: 20px 48px;
            text-decoration: none;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(255, 122, 80, 0.3);
        }

        .btn-contact:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(255, 122, 80, 0.4);
        }
        
        .btn-contact span {
            margin-left: 15px;
            transition: transform 0.3s ease;
        }
        
        .btn-contact:hover span {
            transform: translateX(8px);
        }

        .footer-note {
            margin-top: 60px;
            font-size: 12px;
            color: #444;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .footer-note a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-note a:hover {
            color: var(--accent-color);
        }

        /* Responsive Mobile */
        @media (max-width: 1200px) {
            .branding-side, .content-side { padding: 60px; }
        }

        @media (max-width: 992px) {
            .wrapper { grid-template-columns: 1fr; }
            .branding-side { 
                border-right: none; 
                border-bottom: 1px solid rgba(255,255,255,0.05); 
                padding: 60px 40px; 
                min-height: auto;
            }
            .content-side { padding: 60px 40px; }
            .hero-text h1 { font-size: 56px; }
            .branding-side {
                justify-content: flex-start;
            }
            .hero-text {
                margin: 40px 0;
            }
            .footer-note {
                margin-top: 40px;
            }
        }
        
        @media (max-width: 600px) {
            .hero-text h1 { font-size: 40px; }
            .info-card .value { font-size: 24px; }
            .status-box h3 { font-size: 28px; }
            .btn-contact { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <div class="wrapper">
        <div class="branding-side">
            <div class="logo-container">
                <img src="/images/simora_logo.png" alt="SIMORA Logo">
            </div>

            <div class="hero-text">
                <h2>THE KINETIC ARCHIVE</h2>
                <h1>Precision<br>Performance<br><span>Redefined.</span></h1>
                <p class="hero-description">
                    Mengelola data performa atlet dengan prestise editorial dan akurasi laboratorium.
                </p>
            </div>

            <p class="footer-note">© {{ date('Y') }} SIMORA PERFORMANCE MANAGEMENT SYSTEMS BY <a href="https://kuukok.my.id" target="_blank">KUUKOK.MY.ID</a>.</p>
        </div>

        <div class="content-side">
            <div class="status-box">
                <h3>Sistem Maintenance</h3>
                <p>
                    Saat ini kami sedang melakukan pembaharuan infrastruktur untuk meningkatkan pengalaman monitoring atlet SIMORA yang lebih cepat dan akurat.
                </p>

                <div class="info-card">
                    <label>Estimasi Kembali Online</label>
                    <div class="value">{{ strtoupper($estimasi_selesai) }}</div>
                </div>

                <a href="https://instagram.com/kuukok.id" class="btn-contact" target="_blank">
                    Hubungi Admin <span>&rarr;</span>
                </a>
            </div>
        </div>
    </div>

</body>
</html>
