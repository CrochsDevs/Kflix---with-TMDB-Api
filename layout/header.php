<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KFLIX - Watch Free Movies</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎬</text></svg>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/modal.css">
    <style>
        /* Para hindi mag-overlap ang fixed header */
        body {
            padding-top: 70px;
            margin: 0;
            background-color: #0a0a0a;
            color: #ffffff;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .netflix-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(to bottom, #000, #0a0a0a);
            border-bottom: 1px solid #333;
            height: 70px;
        }
        
        .nav-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .logo-section h1 {
            color: #e50914;
            font-size: 1.8rem;
            margin: 0;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .nav-links {
            display: flex;
            gap: 25px;
        }
        
        .nav-links a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            transition: color 0.3s;
        }
        
        .nav-links a:hover,
        .nav-links a.active {
            color: #e50914;
        }
    </style>
    <script>
        // FORCE TITLE TO BE KFLIX - PREVENT ANY OVERRIDES
        (function() {
            // Set initial title
            document.title = "KFLIX - Watch Free Movies";
            
            // Block any attempts to change title to "Google"
            let currentTitle = "KFLIX - Watch Free Movies";
            
            // Override the title setter
            Object.defineProperty(document, 'title', {
                get: function() {
                    return currentTitle;
                },
                set: function(value) {
                    // Log any title change attempts for debugging
                    console.log('Title change attempted:', value);
                    
                    // Block if it's "Google", otherwise allow
                    if (value !== "Google") {
                        currentTitle = value;
                    } else {
                        console.warn('Blocked attempt to set title to Google');
                        currentTitle = "KFLIX - Watch Free Movies";
                    }
                    
                    // Update the actual title
                    document.querySelector('title').textContent = currentTitle;
                }
            });
        })();
    </script>
</head>
<body>
    <nav class="netflix-nav" id="netflixNav">
        <div class="nav-content">
            <div class="logo-section">
                <h1 class="netflix-logo">KFLIX</h1>
            </div>
            <div class="nav-links">
                <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a>
                <a href="tvshow.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'tvshow.php' ? 'active' : ''; ?>">TV Shows</a>
                <a href="movie.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'movie.php' ? 'active' : ''; ?>">Movies</a>
                <a href="newpopular.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'newpopular.php' ? 'active' : ''; ?>">New & Popular</a>
                <a href="watchlist.php" id="myListLink" class="<?php echo basename($_SERVER['PHP_SELF']) == 'watchlist.php' ? 'active' : ''; ?>">My List</a>
            </div>
        </div>
    </nav>