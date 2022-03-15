<?php
/**
 * Welcome page template
 *
 * @package Tutor\Welcome
 *
 * @since v2.0.0
 */

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Welcome Page</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="./style.css">
        <style type="text/css">
            .tutor-updated-cards img {
  max-width: 100%;
  height: auto;
  user-select: none;
  pointer-events: none;
}
.tutor-w-mt {
  margin-top: 40px;
}
.tutor-w-body-section {
  margin-top: -180px;
}
.tutor-w-pl {
  padding-left: 35px;
}
.tutor-header-section {
  padding-top: 140px;
  padding-bottom: 290px;
  background-color: #bed6fa;
  background-image: url(./image/tutor-welcome-hero.png);
  background-size: contain;
  background-position: bottom right;
  background-repeat: no-repeat;
}
.tutor-header-content {
  max-width: 690px;
  font-family: "Inter", sans-serif;
  padding-top: 45px;
  font-size: 32px;
  line-height: 50px;
  font-weight: 400;
  color: #2d2525;
}
.tutor-welcome-container {
  max-width: 1550px;
  margin: auto;
}
.tutor-welcome-card-column-left {
  max-width: 46%;
}
.tutor-welcome-card-column-right {
  max-width: 54%;
}
.tutor-welcome-feature-column-left {
  max-width: 52%;
}
.tutor-welcome-feature-column-right {
  max-width: 48%;
}
.tutor-updated-features {
  place-content: center;
}
.tutor-updated-features img {
  border-radius: 18px;
}
.tutor-welcome-footer-section {
  padding-top: 100px;
  padding-bottom: 245px;
}
.tutor-welcome-btn {
  font-family: "Inter", sans-serif;
  font-size: 18px;
  line-height: 32px;
  font-weight: 400;
  border-radius: 6px;
  transition: 0.3s linear;
}
.tutor-start-buid-btn {
  color: #fff;
  background-color: #3e64de;
  padding: 16px 100px;
}
.tutor-start-buid-btn:hover {
  color: white;
  box-shadow: 0px 6px 12px 0px rgb(9 30 66 / 20%);
  text-decoration: none;
}
.tutor-pricing-btn {
  color: #3e64de;
  background-color: transparent;
  border: 1px solid #3e64de;
  padding: 16px 60px;
}
.tutor-pricing-btn:hover {
  color: #fff;
  background-color: #3e64de;
  text-decoration: none;
}

        </style>

    </head>

    <body>
        <header>
            <div class="tutor-header-section">
                <div class="container">
                    <div class="row">
                        <div class="col-auto">
                            <div class="tutor-logo-section">
                                <a href="https://www.themeum.com/product/tutor-lms/">
                                    <img src="./image/tutor-logo.png" alt="tutor logo">
                                </a>
                            </div>
                            <div class="tutor-header-content">
                                Welcome to Tutor LMS 2.0: Redefining eLearning on WordPress
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <div class="tutor-updated-cards tutor-w-body-section">
            <div class="tutor-welcome-container container">
                <div class="tutor-updated-cards-section-1">
                    <div class="tutor-updated-features d-flex">
                        <div class="tutor-welcome-card-column-left">
                            <div class="tutor-w-feature-card text-center">
                                <img src="./image/tutor-welcome-card-3.png" alt="updated dashboard">
                            </div>    
                        </div>
                        <div class="tutor-welcome-card-column-right tutor-w-pl">
                            <div class="tutor-w-feature-card text-center">
                                <img src="./image/tutor-welcome-card-2.png" alt="design system">
                                <img class="tutor-w-mt" src="./image/tutor-welcome-card-4.png" alt="advanced Analyst">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tutor-updated-cards-section-2">
                    <div class="col-auto">
                        <div class="tutor-w-feature-card text-center tutor-w-mt">
                            <img src="./image/tutor-welcome-card-1.png" alt="customize feature">
                        </div>   
                    </div>
                </div>
                <div class="tutor-updated-cards-section-3">
                    <div class="tutor-updated-features d-flex tutor-w-mt">
                        <div class="tutor-welcome-feature-column-left">
                            <div class="tutor-w-feature-card text-center">
                                <img src="./image/tutor-welcome-card-5.png" alt="updated notification">
                            </div>    
                        </div>
                        <div class="tutor-welcome-feature-column-right tutor-w-pl">
                            <div class="tutor-w-feature-card text-center">
                                <img src="./image/tutor-welcome-card-6.png" alt="add new features">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tutor-welcome-footer-section">
            <div class="container d-flex justify-content-center">
                <div class="col-auto">
                    <div class="tutor-welcome-start-btn">
                        <a class="tutor-welcome-btn tutor-start-buid-btn" href="https://www.themeum.com/product/tutor-lms/">Let’s Start Building</a>
                    </div> 
                </div>
                <div class="col-auto">
                    <div class="tutor-welcome-pricing-btn">
                        <a class="tutor-welcome-btn tutor-pricing-btn" href="https://www.themeum.com/tutor-lms/pricing/">Get Tutor LMS Pro - 20% off</a>
                    </div> 
                </div>
            </div>
        </div>
    
    </body> 
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</html>

