<?php
/**
 * Elgg visual captcha plugin captcha hook view override.
 *
 * @package ElggVisualCaptcha
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Brett Profitt
 * @copyright Brett Profitt 2008-2009
 * @link http://elgg.org/
 */

// create a new instances for this.
$captcha = new ElggVisualCaptcha();
$images = $captcha->images;

$instance_token_field = elgg_view('input/hidden', array(
	'internalname' => 'visual_captcha_token',
	'value' => $captcha->token
));

$click_order_field = elgg_view('input/hidden', array(
	'internalname' => 'visual_captcha_click_order',
	'value' => ''
));

$language_hints = $captcha->getLanguageHintStrings();
$click_order = '';
$i = 1;
$count = count($language_hints);

foreach ($language_hints as $string) {
	$comma = ($i != $count) ? ',' : '';
	$click_order .= "<span class=\"visualCaptchaLanguageHint hintNumber$i\">$string$comma</span>";
	$i++;
}

$selection = '<ul class="visualCaptchaChoices">';

foreach ($images as $image) {
	$url = $image->getImgURL($captcha->token);
	$token = $image->getImgToken($captcha->token);

	$selection .= "<li><a><img src=\"$url\" id=\"$token\" width=\"75\" height=\"75\" /></a></li>";
}

$selection .= '</ul>';

$reset_link = "<a class=\"visualCaptchaReset\">" . elgg_echo('visual_captcha:reset_images') . '</a>';

?>

<div class="visualCaptcha" id="<?php echo $captcha->token; ?>">
	<h2><?php echo elgg_echo('visual_captcha:enter_captcha'); ?></h2>
	<h3><?php echo $click_order?></h3>
	<?php echo $reset_link; ?>
	<?php echo $selection; ?>
	<?php echo $instance_token_field; ?>
	<?php echo $click_order_field; ?>
</div>

<script type="text/javascript">
$(document).ready(function() {
	var imgMax = <?php echo $vars['config']->visual_captcha_images_match; ?>;
	var token = '<?php echo $captcha->token; ?>';
	var clickOrderTokens = [];
	var numClicked = 0;

	$('#' + token + ' ul.visualCaptchaChoices li a img').click(function() {
		// you lie, jQuery docs. This is supposed to return an empty string
		// if it's not been set...
		clicked = $(this).data('clicked');
		clicked = (typeof clicked == 'undefined') ? false : clicked;

		if (!clicked && numClicked++ < imgMax) {
			// mark as clicked
			// save style info for reset
			$(this).data('clicked', true)
				.data('background-color', $(this).css('background-color'))
				.data('-moz-border-radius', $(this).css('-moz-border-radius', 8))
				.data('-webkit-border-radius', $(this).css('-webkit-border-radius', 8));

			$(this).css('background-color', '#FFFF66')
				.css('-moz-border-radius', 8)
				.css('-webkit-border-radius', 8);

			// mark off language hint
			$('#' + token + ' .hintNumber' + numClicked).css('text-decoration', 'line-through');

			// set click order
			clickOrderTokens.push($(this).attr('id'));

			// grey out other choices when at max
			if (numClicked == imgMax) {
				$('#' + token + ' ul.visualCaptchaChoices li a img').each(function(i, e) {
					clicked = $(e).data('clicked');
					clicked = (typeof clicked == 'undefined') ? false : clicked;

					if (!clicked) {
						$(e).css('opacity', .15);
					}
				});
			}
		}
	});

	// reset all attributes
	$('#' + token + ' .visualCaptchaReset').click(function() {
		numClicked = 0;
		clickOrderTokens = [];
		$('#' + token + ' .visualCaptchaLanguageHint').css('text-decoration', 'inherit');

		$('#' + token + ' ul.visualCaptchaChoices li a img').each(function(i, e) {
			clicked = $(e).data('clicked');
			clicked = (typeof clicked == 'undefined') ? false : clicked;
			$(e).css('opacity', 1);

			if (clicked) {
				$(e).data('clicked', false);
				$(e).css('background-color', $(e).data('background-color'))
					.css('-moz-border-radius', $(e).data('-moz-border-radius'))
					.css('-webkit-border-radius', $(e).data('-webkit-border-radius'));
			}
		});
	});

	// save data when submitting the parent form.
	$('#' + token).parents('form').submit(function() {
		orderInput = $('#' + token + ' input[name=visual_captcha_click_order]');
		orderInput.val(clickOrderTokens.join(','));

		return true;
	});
});
</script>