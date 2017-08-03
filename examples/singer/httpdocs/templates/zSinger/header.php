<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>

    <!-- Basic Page Needs
  ================================================== -->
	<meta charset="utf-8">
	<title>zSinger</title>
	<meta name="description" content="Free Responsive Html5 Css3 Templates | Zerotheme.com">
	<meta name="author" content="https://www.zerotheme.com">
	
    <!-- Mobile Specific Metas
	================================================== -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    
    <!-- CSS
	================================================== -->
  	<link rel="stylesheet" href="{{templatePath}}css/zerogrid.css">
	<link rel="stylesheet" href="{{templatePath}}css/style.css">
	
	<!-- Custom Fonts -->
    <link href="{{templatePath}}font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
	
	
	<link rel="stylesheet" href="{{templatePath}}css/menu.css">
	<script src="{{templatePath}}js/jquery1111.min.js" type="text/javascript"></script>
	<script src="{{templatePath}}js/script.js"></script>
	
	<!-- Owl Carousel Assets -->
    <link href="{{templatePath}}owl-carousel/owl.carousel.css" rel="stylesheet">
	
	<!--[if lt IE 8]>
       <div style=' clear: both; text-align:center; position: relative;'>
         <a href="http://windows.microsoft.com/en-US/internet-explorer/Items/ie/home?ocid=ie6_countdown_bannercode">
           <img src="http://storage.ie6countdown.com/assets/100/images/banners/warning_bar_0000_us.jpg" border="0" height="42" width="820" alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today." />
        </a>
      </div>
    <![endif]-->
    <!--[if lt IE 9]>
		<script src="{{templatePath}}js/html5.js"></script>
		<script src="{{templatePath}}js/css3-mediaqueries.js"></script>
	<![endif]-->
	
</head>

<body>
	<div class="wrap-body">
		
		<header class="main-header">
			<div class="zerogrid">
				<div class="t-center">
					<a class="site-branding" href="index.html">
						<img src="{{templatePath}}images/logo.png" width="250px"/>	
					</a><!-- .site-branding -->
				</div>
				<div class="row">
					<div class="col-2-3">
						<!-- Menu-main -->
						<div id='cssmenu'>
							<ul>
							   <li class="active"><a href='{{Link|Get|/}}'><span>Home</span></a></li>
							   <li><a href='{{Link|Get|/about/}}'><span>About</span></a></li>
							   <li><a href='{{Link|Get|/blog/}}'><span>Blog</span></a></li>
							   <li class='last'><a href='{{Link|Get|/contacts/}}'><span>Contacts</span></a></li>
							</ul>
						</div>
					</div>
					<div class="col-1-3">
						<div class="top-search">
							<form id="form-container" action="">
								<!--<input type="submit" id="searchsubmit" value="" />-->
								<a class="search-submit-button" href="javascript:void(0)">
									<i class="fa fa-search"></i>
								</a>
								<div id="searchtext">
									<input type="text" id="s" name="s" placeholder="Search">
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</header>