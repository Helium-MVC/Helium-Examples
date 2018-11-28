<?php

PVTemplate::setSiteTitle($user -> first_name . ' ' . $user -> last_name);
 
PVTemplate::appendSiteMetaTags('<meta name="description" content="'. $this -> Format -> ogTag(PVTools::truncateText($user -> bio, 100)) .'" />');

PVTemplate::appendSiteMetaTags('<meta property="og:title" content="'. $this -> Format -> ogTag($user -> first_name . ' ' . $user -> last_name).' "/>');
PVTemplate::appendSiteMetaTags('<meta property="og:description" content="'. $this -> Format -> ogTag(PVTools::truncateText($user -> bio, 100)) .'">');
PVTemplate::appendSiteMetaTags('<meta property="og:url" content="' . PVTools::getCurrentUrl() .'"/>');
PVTemplate::appendSiteMetaTags('<meta property="og:site_name" content="Helium MVC"/>');
PVTemplate::appendSiteMetaTags('<meta property="og:type" content="website"/>');
if($user -> image_id):
	PVTemplate::appendSiteMetaTags('<meta property="og:image" content="'. $this -> Format -> parseImage($user -> image_large_url) .'" />');
 endif;

PVTemplate::appendSiteMetaTags('<meta name="twitter:card" content="summary">');
PVTemplate::appendSiteMetaTags('<meta name="twitter:site" content="@he2mvc">');
PVTemplate::appendSiteMetaTags('<meta name="twitter:creator" content="@he2mvc">');
PVTemplate::appendSiteMetaTags('<meta name="twitter:url" content="' . PVTools::getCurrentUrl()  . '">');
PVTemplate::appendSiteMetaTags('<meta name="twitter:title" content="'. $this -> Format -> ogTag($user -> first_name . ' ' . $user -> last_name).'">');
PVTemplate::appendSiteMetaTags('<meta name="twitter:description" content="'. $this -> Format -> ogTag(PVTools::truncateText($user -> bio, 100)) .'">');

?>

<div class="container mt-5">
	<?php if($this -> Session -> get('user_id') == $user -> user_id): ?>
		<a class="btn btn-success" href="/users/account/<?= $user -> user_id; ?>"><i class="fas fa-pencil-alt"></i> Update</a>
	<?php endif; ?>
	<div class="row ">
		<div class="col-sm-3">
			<?php if($user -> image_id): ?>
				<div class="text-center" >
					<img class="img-fluid" src="<?= $this -> Format -> parseImage($user -> image_medium_url); ?>" />
				</div>
			<?php endif; ?>
		</div>
		<div class="col-sm-9">
			<h1><?= $user -> first_name; ?> <?= $user -> last_name; ?></h1>
			<?= $user -> bio; ?>
			
			<hr />
			<?php include('_posts.html.php'); ?>
		</div>
	</div>
</div>