<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$identifier = 'daily_deals';
$title = 'Daily deals block homepage';
$contentCmsBlock = <<<EOT
<div id="$identifier">
	<div style="padding-top: 35px;">
	    <div class="container">
		<div class="row">
		    <div class="col-sm-12">
		        <h2 class="filter-title"><span class="content"><strong>Daily Deals</strong></span></h2>
		        <div id="featured_product" class="owl-top-narrow">
				{{block type="filterproducts/featured_home_list" name="my.block.name" template="filterproducts/list.phtml"}}
		        </div>
		        <script type="text/javascript">
		            jQuery(function($){
		                $("#featured_product .filter-products .owl-carousel").owlCarousel({lazyLoad: true,    itemsCustom: [ [0, 1], [320, 1], [480, 2], [768, 3], [992, 4], [1280, 5] ],    responsiveRefreshRate: 50,    slideSpeed: 200,    paginationSpeed: 500,    scrollPerPage: false,    stopOnHover: true,    rewindNav: true,    rewindSpeed: 600,    pagination: false,    navigation: true,    autoPlay: true,    navigationText:["<i class='icon-left-open'></i>","<i class='icon-right-open'></i>"]});
		            });
		        </script>
		    </div>
		</div>
	    </div>
	</div>
</div>
EOT;
$cmsBlock = Mage::getModel('cms/block')->load($identifier);
if ($cmsBlock->isObjectNew()) {
    $cmsBlock->setTitle($title)
        ->setContent($contentCmsBlock)
        ->setIdentifier($identifier)
        ->setStores(0)
        ->setIsActive(true);
} else {
    $cmsBlock->setTitle($title)
        ->setContent($contentCmsBlock)
        ->setStores(0)
        ->setIsActive(true);
}

$cmsBlock->save();

$identifier = '4_boxes_banners';
$title = '4 boxes banners homepage';
$contentCmsBlock = <<<EOT
<div id="$identifier">
	<div class="single-images border-radius">
		<div class="row">
		    <div class="col-sm-3 col-xs-6">
		        <a class="image-link" href="#"><h1>Shoes</h1>
		            <img src="https://s11.postimg.org/djh14i0xf/Capture.png"  alt="" />
		        </a>
		    </div>
		    <div class="col-sm-3 col-xs-6" style="padding-left:5px;">
		        <a class="image-link" href="#"><h1>Caps</h1>
		            <img src="https://s11.postimg.org/5f8wzreib/hat.png" alt="" />
		        </a>
		    </div>
		    <div class="col-sm-3 col-xs-6" >
		        <a class="image-link" href="#"><h1>Hoodies</h1>
		            <img src="https://s11.postimg.org/904sizj1v/hoodie.png" alt="" />
		        </a>
		    </div>
		    <div class="col-sm-3 col-xs-6" style="padding-left:5px;">
		        <a class="image-link" href="#"><h1>Jeans</h1>
		            <img src="https://s11.postimg.org/k0zxo0bar/woman.png" alt="" />
		        </a>
		    </div>
		</div>
	</div>
</div>
EOT;
$cmsBlock = Mage::getModel('cms/block')->load($identifier);
if ($cmsBlock->isObjectNew()) {
    $cmsBlock->setTitle($title)
        ->setContent($contentCmsBlock)
        ->setIdentifier($identifier)
        ->setStores(0)
        ->setIsActive(true);
} else {
    $cmsBlock->setTitle($title)
        ->setContent($contentCmsBlock)
        ->setStores(0)
        ->setIsActive(true);
}
$cmsBlock->save();


$identifier = 'info_boxes_homepage';
$title = 'Info boxes Homepage';
$contentCmsBlock = <<<EOT
<div id="$identifier">
	<div class="homepage-bar">
	    <div class="container">
		<div class="row">
		    <div class="col-md-4">
		        <i class="icon-truck" style="font-size:36px;"></i><div class="text-area"><h3>FREE SHIPPING & RETURN</h3><p>Free shipping on all orders over $99.</p></div>
		    </div>
		    <div class="col-md-4">
		        <i class="icon-dollar"></i><div class="text-area"><h3>MONEY BACK GUARANTEE</h3><p>100% money back guarantee.</p></div>
		    </div>
		    <div class="col-md-4">
		        <i class="icon-lifebuoy" style="font-size:32px;"></i><div class="text-area"><h3>ONLINE SUPPORT 24/7</h3><p>Lorem ipsum dolor sit amet.</p></div>
		    </div>
		</div>
	    </div>
	</div>
</div>
EOT;
$cmsBlock = Mage::getModel('cms/block')->load($identifier);
if ($cmsBlock->isObjectNew()) {
    $cmsBlock->setTitle($title)
        ->setContent($contentCmsBlock)
        ->setIdentifier($identifier)
        ->setStores(0)
        ->setIsActive(true);
} else {
    $cmsBlock->setTitle($title)
        ->setContent($contentCmsBlock)
        ->setStores(0)
        ->setIsActive(true);
}
$cmsBlock->save();

$identifier = 'shoes_board_banner_homepage';
$title = 'Shoes and Board Banners Homepage';
$contentCmsBlock = <<<EOT
<div id="$identifier">
	<div class="single-images border-radius">
		<div class="row">
		    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
		        <a class="image-link" href="#">
		            <img src="https://s11.postimg.org/djrwqxypv/shoes.png" alt="" />
		        </a>
		    </div>
		    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12" >
		        <a class="image-link" href="#">
		            <img src="https://s11.postimg.org/oi3694nb7/board.png" alt="" />
		        </a>
		    </div>
		</div>
	</div>
</div>
EOT;
$cmsBlock = Mage::getModel('cms/block')->load($identifier);
if ($cmsBlock->isObjectNew()) {
    $cmsBlock->setTitle($title)
        ->setContent($contentCmsBlock)
        ->setIdentifier($identifier)
        ->setStores(0)
        ->setIsActive(true);
} else {
    $cmsBlock->setTitle($title)
        ->setContent($contentCmsBlock)
        ->setStores(0)
        ->setIsActive(true);
}
$cmsBlock->save();

$installer->endSetup();
