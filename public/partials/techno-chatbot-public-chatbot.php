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
		<span>
			<?php echo $headertxt; ?>
			<?php if( $livechat_enabled ){ ?>
				<span id="techno-support-status-dot" class="techno-status-dot offline" title="Support Offline"></span>
			<?php } ?>
		</span>
		<div class="techno-chatbot-menu">
			<a id="techno-chatbot-menu-trigger">Menu</a>
			<ul id="techno-chatbot-menu-list">
				<li><a id="techno-chatbot-disclaimer">Disclaimer</a></li>
				<?php if( $disclaimerEnabled ){ ?>
					<li><a class="techno-chatbot-reset">Restart</a></li>
				<?php } ?>
			</ul>
		</div>
		<button type="button" id="techno-chatbot-close">×</button>
	</div>

	<div id="techno-chatbot-messages"></div>

	<div class="techno-chatbot-input-wrapper">
		<input type="text" id="techno-chatbot-input" placeholder="<?php echo $inputtxt; ?>" max-length="250" />
		<button type="button" id="techno-chatbot-send">
			<?php echo $sendbtn; ?>
		</button>
	</div>

	<div style="position: absolute;left: 0;font-size: 12px;color: var(--techno-admin-bubble-text);background: var(--techno-admin-bubble-bg);bottom: 35px;height: 15px;padding: 2px 5px;border-radius: 5px;"> Powered by <a href="#" target="_blank">Technodream</a> </div>
</div>

<?php if( $disclaimerEnabled && $disclaimerFullMsg != '' ){ ?>
	<div id="techno-chatbot-disclaimer-modal">
		<div class="techno-chatbot-disclaimer-container">
			<div class="techno-chatbot-disclaimer-content">
				<button class="close-btn">&times;</button>
				<div class="content"><?php echo $disclaimerFullMsg; ?></div>
			</div>
		</div>
	</div>
<?php } ?>