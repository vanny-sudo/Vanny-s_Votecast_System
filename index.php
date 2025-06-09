<?php

require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Polls</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">

    <link href="bootstrap.min.css" rel="stylesheet">
    <link href="templatemo-topic-listing.css" rel="stylesheet">
</head>

<body>
    <div class="main-bg">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="index.html">
                    <i class="bi-back"></i>
                    <span>Voting System</span>
                </a>

                <div class="d-lg-none ms-auto me-4">
                    <a href="#top" class="navbar-icon bi-person smoothscroll"></a>
                </div>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-lg-5 me-lg-auto">
                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="index.php" style="color: #333;">Home</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="register.php" style="color: #333;">Create Account</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="login.php" style="color: #333;">Login</a>
                        </li>
                    </ul>

                    <div class="d-none d-lg-block">
                        <a href="#top" class="navbar-icon bi-person smoothscroll"></a>
                    </div>
                </div>
            </div>
        </nav>

        <section class="hero-section d-flex justify-content-center align-items-center" id="section_1">
            <div class="container">
                <div class="row">

                    <div class="col-lg-8 col-12 mx-auto">
                        <h1 class="text-white text-center">Welcome to the <br /> Voting System</h1>

                        <h6 class="text-center">Participate in polls and make your voice heard!</h6>

                        <form method="get" class="custom-form mt-4 pt-2 mb-lg-0 mb-5" role="search">
                            <div class="input-group input-group-lg">
                                <a href="home.php" class="btn btn-primary w-50">View Available Polls</a>
                                <a href="create_poll.php" class="btn btn-outline-light w-50" style="color: #333; background: gray">Create a Poll</a>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </section>
    </div>
</body>

</html>