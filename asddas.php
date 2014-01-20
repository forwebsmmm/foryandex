<div id="myCarousel" class="carousel slide">

	<div class="carousel-inner">
		<?php if($GLOBALS['contactPage']) : ?>
			<div class="map">
				<div id=map class='item active'></div>
				<div class=contact>
					<h3 class=wbg><?php echo the_title()?></h3>
					<?php
							while ( have_posts() ) {
								the_post();
								the_content();
							}
					?>
<!--					<a class=mail href="--><?php //echo bloginfo('admin_email'); ?><!--">--><?php //echo bloginfo('admin_email'); ?><!--</a>-->
				</div>
			</div>
		<?php else :

			$args = array(
				'post_type' => 'attachment',
				'numberposts' => -1,
				'post_status' => 'inherit',
				'post_parent' =>  $post->ID
			);
			$attachments = get_posts( $args );

			// выводим дефолтную gallery
			if(!count($attachments)) {
				$args = array(
					'post_type' => 'attachment',
					'numberposts' => -1,
					'post_status' => 'inherit',
					'post_parent' =>  1
				);
				$the_query = new WP_Query( $args );
				$the_query->the_post();
				$attachments = $the_query->get_posts( $args );
			}

			if ( $attachments ) {
				$i = 1;
				foreach ( $attachments as $attachment ) {
					$active = ($i == 1) ? 'active' : '';
					$image = wp_get_attachment_image_src( $attachment->ID, 'full' );
					$image_src = $image[0];
					echo "<div class='item $active'>";
					echo "<img src='$image_src' alt=''>";
					echo "</div>";
					$i++;
				}
			}
		endif;

		?>

	</div>
	<!---->
	<!--	<div class="carousel-inner">-->
	<!---->
	<!--		<div class="item active">-->
	<!--			<img src="http://twitter.github.io/bootstrap/assets/img/examples/slide-01.jpg" alt="">-->
	<!--			<div class="container">-->
	<!--				<div class="carousel-caption">-->
	<!--					<h1>Example headline.</h1>-->
	<!--					<p class="lead">Cras justo odio, dapibus ac facilisis in, egestas eget quam. Donec id elit non mi porta gravida at eget metus. Nullam id dolor id nibh ultricies vehicula ut id elit.</p>-->
	<!--					<a class="btn btn-large btn-primary" href="#">Sign up today</a>-->
	<!--				</div>-->
	<!--			</div>-->
	<!--		</div>-->
	<!--	</div>-->

	<?php if(!$GLOBALS['contactPage']) : ?>
		<a class="left carousel-control" href="#myCarousel" data-slide="prev">&lsaquo;</a>
		<a class="right carousel-control" href="#myCarousel" data-slide="next">&rsaquo;</a>
	<?php endif; ?>
</div><!-- /.carousel -->
<?php wp_reset_postdata(); ?>
