<?php
/* @package JMod Contact for Joomla 3.0!  
 * @link       http://jmodules.com/ 
 * @copyright (C) 2012- Sean Casco
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html 
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/*=================================================================
Getting all param of module
Here all params set by admin for this module are catching in varuables for further use
=================================================================*/

  /* Module suffix params*/
  $mod_class_suffix = $params->get('moduleclass_sfx', '');
  
  /* Fields label params*/
  $emaillabel = $params->get('email_label', 'Email:');
  $subjectlabel = $params->get('subject_label', 'Subject:');
  $messagelabel = $params->get('message_label', 'Message:');
  $recipient_email_id = $params->get('recipient_email_id', '');
  /*==========================================================*/
  
  /* Custom Fields params*/
  $txtfld1label = $params->get('custom_field_1', 'Custom1:');
  $txtfld1enable = $params->get('custom_field_1_enable', false);
  $txtfld2label = $params->get('custom_field_2', 'Custom2:');
  $txtfld2enable = $params->get('custom_field_2_enable', false);
  $txtfld3label = $params->get('custom_field_3', 'Custom3:');
  $txtfld3enable = $params->get('custom_field_3_enable', false);
  /*==========================================================*/
  
  /* message setting param values*/
  $button_text = $params->get('button_text', 'Send Message');
  $thanks_message = $params->get('thanks_message', 'Thank you for your contact.');
  $thanks_message_text_color = $params->get('thanks_message_text_color', '#FF0000');
  $error_message = $params->get('error_message', 'Message could not be sent to recipient. Please your entries and make sure all entries are right and try again.');

  $email_required_message = $params->get('email_required_message', 'Please enter your email');
  $invalid_email_message = $params->get('invalid_email_message', 'Please enter a valid email');
  $subject_required_message = $params->get('subject_required_message', 'Please enter your mail subject');
  $msg_required_message = $params->get('msg_required_message', 'Please enter your mail message');

  $fromName = @$params->get('from_name', 'Contact Form');
  $fromEmail = @$params->get('from_email', 'contact_form@yoursite.com');
  /*==========================================================*/
  
  /* Fields widths param values*/
  $emailwidth = $params->get('email_width', '15');
  $subjectwidth = $params->get('subject_width', '25');
  $messagewidth = $params->get('message_width', '25');
  $buttonwidth = $params->get('button_width', '50');
  /*==========================================================*/
  
  /* mail antispam params*/
  $enable_anti_spam = $params->get('enable_anti_spam', false);
  $anti_spam_question = $params->get('anti_spam_q', 'One + One =(number)?');
  $anti_spam_answer = $params->get('anti_spam_a', '2');
  /*==========================================================*/
  
  /* recaptcha params*/
  $enable_recaptcha   = $params->get('enable_recaptcha', false);
  $recaptcha_public   = $params->get('recaptcha_public_key', false);
  $recaptcha_private  = $params->get('recaptcha_private_key', false);
  
  
  $disable_https = $params->get('disable_https', false);   
  $pre_text = $params->get('pre_text', '');
  /*==========================================================*/
  
  // include the recaptcha library if necessary
  if ($enable_recaptcha)
  {
    require_once('recaptcha_JMod.php');
    $resp = recaptcha_check_answer($recaptcha_private,$_SERVER["REMOTE_ADDR"],@$_POST["recaptcha_challenge_field"],@$_POST["recaptcha_response_field"]);
  }
  $recaptcha_error = null;

  $exact_url = $params->get('exact_url', true);
  if (!$exact_url) {
    $url = JURI::current();
  }
  else {
    if (!$disable_https) {
    $url = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    }
    else {
    $url = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    }
  }
  
  $err = '';
  /*============= IF isset $_POST====================*/
  if (isset($_POST["email"]))
  {
    if ($enable_anti_spam && $_POST["anti_spam_answer"] != $anti_spam_answer)  /* anti spam question */
    {
      $err = '<span style="color: #f00;">' . JText::_('Wrong anti-spam answer') . '</span>';
    }
    elseif ($enable_recaptcha && !$resp->is_valid) // reCaptcha spam
    {
      $err = '<span style="color: #f00;">' . JText::_('The reCAPTCHA wasn\'t entered correctly. Go back and try it again.' . '(reCAPTCHA said: ' . $resp->error . ')') . '</span>';
      $recaptcha_error = $resp->error;
    }
    
    if ($_POST["email"] === "")// validation for empty email
    {
      $err = '<span style="color: #f00;">' . $email_required_message . '</span>';
    }
    elseif (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $_POST["email"]))//validation for invalide email
    {
      $err = '<span style="color: #f00;">' . $invalid_email_message . '</span>';
    }
    elseif ($_POST["subject"] === "")// validation for empty subject
    {
      $err = '<span style="color: #f00;">' . $subject_required_message . '</span>';
    }
    elseif ($_POST["message"] === "")// validation for empty message
    {
      $err = '<span style="color: #f00;">' . $msg_required_message . '</span>';
    }

    if ($err == '') {
      $mySubject = $_POST["subject"];
      $myMessage = 'You received a message from '. $_POST["email"] ."\n\n". $_POST["message"];
      if(isset($_POST['custome_field1']) && $_POST['custome_field1']!=''){
        $myMessage .= "\n\n".$txtfld1label." : " . $_POST['custome_field1'];    
      }
      if(isset($_POST['custome_field2']) && $_POST['custome_field2']!=''){
        $myMessage .= "\n\n".$txtfld2label." : " . $_POST['custome_field2'];    
      }
      if(isset($_POST['custome_field3']) && $_POST['custome_field3']!=''){
        $myMessage .= "\n\n".$txtfld3label." : " . $_POST['custome_field3'];    
      }
      
      $mailSender = &JFactory::getMailer();
      $mailSender->addRecipient($recipient_email_id);
  
      $mailSender->setSender(array($fromEmail,$fromName));
      $mailSender->addReplyTo(array( $_POST["email"], '' ));
  
      $mailSender->setSubject($mySubject);
      $mailSender->setBody($myMessage);
  
      if (!$mailSender->Send()) {
        $myReplacement = '<span style="color: #f00;">' . $error_message . '</span>';
        print $myReplacement;
        return true;
      }
      else {
        $myReplacement = '<span style="color: '.$thanks_message_text_color.';">' . $thanks_message . '</span>';
        print $myReplacement;
        return true;
      }
    }
  }
  /*============= end of isset $_POST====================*/
  if ($recipient_email_id === "") {
    $myReplacement = '<span style="color: #f00;">Email recipient not define for contact recieving contact email, Please contact to site admin report to this problem .</span>';
    print $myReplacement;
    return true;
  }
  if ($err != '') {
    print $err;
  } ?>
  
  <div class="contact_form <?php echo $mod_class_suffix; ?>">
    <form action="<?php echo $url; ?>" method="post">
      <div class="contact_form intro_text <?php echo $mod_class_suffix; ?>"><?php echo $pre_text; ?></div>
   
      <div style="line-height:30px;">
        <?php echo $emaillabel;?><font color="#f00">(*)</font>
      </div>
      <div>
        <input class="contact_form inputbox <?php echo $mod_class_suffix;?>" type="text" name="email" size="<?php echo $emailwidth;?>" value="<?php if ($err != '' && isset($_POST['email'])) { echo $_POST['email'];}?>" />
      </div>
      <div style="line-height:30px;"><?php echo $subjectlabel;?><font color="#f00">(*)</font></div>
      <div>
        <input class="contact_form inputbox <?php echo $mod_class_suffix;?>" type="text" name="subject" size="<?php echo $subjectwidth;?>" value="<?php if ($err != '' && isset($_POST['subject'])) { echo $_POST['subject'];}?>" />
      </div>
      <?php if($txtfld1enable) { ?>
        <div style="line-height:30px;"><?php echo $txtfld1label;?></div>
        <div style="line-height:30px;"><input class="contact_form inputbox <?php echo $mod_class_suffix;?>" type="text" name="custome_field1" size="<?php echo $emailwidth;?>" value="<?php if ($err != '' && isset($_POST['custome_field1'])) { echo $_POST['custome_field1'];}?>" /></div>
      <?php }
      if($txtfld2enable) { ?>
        <div style="line-height:30px;"><?php echo $txtfld2label;?></div>
        <div style="line-height:30px;"><input class="contact_form inputbox <?php echo $mod_class_suffix;?>" type="text" name="custome_field2" size="<?php echo $emailwidth;?>" value="<?php if ($err != '' && isset($_POST['custome_field2'])) { echo $_POST['custome_field2'];}?>" /></div>
      <?php }
      if($txtfld3enable) { ?>
        <div style="line-height:30px;"><?php echo $txtfld3label;?></div>
        <div style="line-height:30px;"><input class="contact_form inputbox <?php echo $mod_class_suffix;?>" type="text" name="custome_field3" size="<?php echo $emailwidth;?>" value="<?php if ($err != '' && isset($_POST['custome_field3'])) { echo $_POST['custome_field3'];}?>" /></div>
      <?php } ?>
      <div style="line-height:30px;"><?php echo $messagelabel;?><font color="#f00">(*)</font></div>
      <div><textarea class="contact_form textarea <?php echo $mod_class_suffix;?>" type="text" name="message" cols="<?php echo $messagewidth;?>" rows="4"><?php if ($err != '' && isset($_POST['message'])) { echo $_POST['message'];}?></textarea></div>
          <?php if($enable_anti_spam) { ?>
        <div style="line-height:30px;"><?php echo $anti_spam_question; ?></div>
        <div style="line-height:30px;">Answer :<font color="#f00">(*)</font></div>
        <div style="line-height:30px;"><input class="contact_form inputbox <?php echo $mod_class_suffix;?>" type="text" name="anti_spam_answer" size="<?php echo $emailwidth; ?>" value="<?php if ($err != '' && isset($_POST['anti_spam_answer'])) { echo $_POST['anti_spam_answer'];} ?>"/></div>
      <?php } ?>
      <?php if ($enable_recaptcha)
      { ?>
        <div style="line-height:30px;"><?php echo recaptcha_get_html($recaptcha_public, $recaptcha_error);?></div>
      <?php } ?>
      <div style="line-height:30px;"><input class="contact_form button <?php echo $mod_class_suffix;?>" type="submit" value="<?php echo $button_text;?>" style="width: <?php echo $buttonwidth;?>%"/></div>
    </form>
	<div style="color:#E0E0E0;margin-bottom:3px;font-size:8px"><a style="text-decoration:none" target="_blank" class="external" href="http://samothrakihotels.com/"><span style="color:#E0E0E0;margin-bottom:3px;font-size:8px">samothraki hotels</span></a></div>
  </div>
  <?php  return true; ?>