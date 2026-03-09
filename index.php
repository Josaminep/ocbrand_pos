<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>OC Brand - Loading</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #000000, #1a1a1a);
            overflow: hidden;
        }

        .logo-box {
            text-align: center;
            animation: fadeIn 1.5s ease-out;
        }

        .logo-box img {
            width: 120px;
            height: 120px;
            border-radius: 20px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
            animation: float 3s ease-in-out infinite;
        }

        .title {
            margin-top: 20px;
            font-size: 32px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 2px;
            text-shadow: 0 0 10px rgba(255,255,255,0.3);
            animation: glow 2s infinite alternate;
        }

        .subtitle {
            margin-top: 5px;
            font-size: 16px;
            color: #cccccc;
            font-weight: 500;
        }

        .loader {
            width: 55px;
            height: 55px;
            border: 6px solid rgba(255,255,255,0.3);
            border-top-color: #ffffff;
            border-radius: 50%;
            margin: 25px auto 0;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes glow {
            from { text-shadow: 0 0 5px rgba(255,255,255,0.2); }
            to { text-shadow: 0 0 15px rgba(255,255,255,0.5); }
        }
    </style>
</head>
<body>
    <div class="logo-box">
        <img src="https://cdn-icons-png.flaticon.com/512/126/126083.png" alt="Logo" />
        <div class="title">OC Brand</div>
        <div class="subtitle">POS & Inventory System</div>
        <div class="loader"></div>
    </div>
<script>
        // Redirect after 3 seconds
        setTimeout(function(){ window.location.href = "home.php"; }, 3000);
</script>
</body>
</html>