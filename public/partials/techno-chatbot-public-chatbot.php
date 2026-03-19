<?php

/**
 * Chatbot Interface
 *
 * @link       https://technodreamwebdesign.com/
 * @since      1.0.0
 *
 * @package    Techno_Chatbot
 * @subpackage Techno_Chatbot/public/partials
 */
?>

<?php if( !empty($icontxt) ){ ?>
	<!-- Floatinng Chat Text-->
	<div id="techno-chatbot-floating-text"><?php echo $icontxt; ?></div>
<?php } ?>

<!-- Floating Chat Icon -->
<div id="techno-chatbot-floating-icon" aria-label="<?php esc_attr_e( 'Open Chatbot', 'techno-chatbot' ); ?>">
	<?php echo $chaticon; ?>
</div>

<!-- Chat Window -->
<div id="techno-chatbot-window" class="techno-chatbot-hidden">

	<div class="techno-chatbot-header">
		<span><?php echo $headertxt; ?></span>
		<div class="techno-chatbot-menu">
			<a id="techno-chatbot-menu-trigger">Menu</a>
			<ul id="techno-chatbot-menu-list">
				<li><a class="techno-chatbot-reset">Restart</a></li>
			</ul>
		</div>
		<button type="button" id="techno-chatbot-close">×</button>
	</div>

	<div id="techno-chatbot-messages"></div>

	<div class="techno-chatbot-input-wrapper">
		<input type="text"
			   id="techno-chatbot-input"
			   placeholder="<?php echo $inputtxt; ?>" />
		<button type="button" id="techno-chatbot-send">
			<?php echo $sendbtn; ?>
		</button>
	</div>

	<div style="position: absolute;left: 0;font-size: 12px;color: var(--techno-admin-bubble-text);background: var(--techno-admin-bubble-bg);bottom: 35px;height: 15px;padding: 2px 5px;border-radius: 5px;"> Powered by <a href="#" target="_blank">Technodream</a> </div>
</div>