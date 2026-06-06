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
<div id="techno-chatbot-floating-icon" class="<?php echo $chaticontype; echo !empty($chaticonval)? ' image' : ' emoji';?>" aria-label="<?php esc_attr_e( 'Open Chatbot', 'techno-chatbot' ); ?>">
	<?php if($chaticontype == 'icon_text'){ ?>
		<?php echo $chaticon; ?>
		<span><?php echo $chaticontxt; ?></span>
	<?php }else{ ?>
		<?php echo $chaticon; ?>
	<?php } ?>
</div>

<!-- Chat Window -->
<div id="techno-chatbot-window" class="techno-chatbot-hidden">

	<div class="techno-chatbot-header">
		<span style="line-height: 100%;">
			<?php echo $headertxt; ?>
			<?php if( $livechat_enabled ){ ?>
				<span id="techno-support-status-dot" class="techno-status-dot offline" title="Support Offline"></span>
			<?php } ?>
			<br/><small style="font-size: 12px;color: #555;background: #fff;height: 15px;padding: 0 5px;border-radius: 5px;display: inline-block;line-height: 15px;"> Powered by <a href="#" target="_blank" style="color: #555;font-weight: 700;">Technodream</a> </small>
		</span>
		<div class="techno-chatbot-menu">
			<a id="techno-chatbot-menu-trigger" title="Show Chat Menu"><svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="30" width="30" xmlns="http://www.w3.org/2000/svg"><circle cx="256" cy="256" r="48"></circle><circle cx="416" cy="256" r="48"></circle><circle cx="96" cy="256" r="48"></circle></svg></a>
			<ul id="techno-chatbot-menu-list">
				<?php if( $disclaimerEnabled ){ ?>
					<li><a id="techno-chatbot-disclaimer">Disclaimer</a></li>
				<?php } ?>
				<li><a id="techno-chatbot-transcript-request"><?php echo $menutranscripttxt; ?></a></li>
				<li><a class="techno-chatbot-reset"><?php echo $menuresettxt; ?></a></li>
			</ul>
			<button type="button" id="techno-chatbot-close"><svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="30" width="30" xmlns="http://www.w3.org/2000/svg"><path d="M289.94 256l95-95A24 24 0 00351 127l-95 95-95-95a24 24 0 00-34 34l95 95-95 95a24 24 0 1034 34l95-95 95 95a24 24 0 0034-34z"></path></svg></button>
		</div>
	</div>

	<div id="techno-chatbot-messages"></div>

	<div class="techno-chatbot-input-wrapper">
		<input type="text" id="techno-chatbot-input" placeholder="<?php echo $inputtxt; ?>" max-length="250" />
		<button type="button" id="techno-chatbot-send">
			<?php echo $sendbtn; ?>
		</button>
	</div>
	<?php if($disclaimer != ''){ ?>
		<div class="techno-chatbot-disclaimer-short"><?php echo $disclaimer; ?></div>
	<?php } ?>
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