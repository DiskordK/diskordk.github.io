<?php
session_start();
error_reporting(0);
define ('TITLE', stripslashes('Œ“œ–¿¬»“‹ œŒ—“'));
define ('NAME', '');
define ('SUBJECT', '');
define ('EMAIL', '');
define ('WEBSITE', '');
define ('MESSAGE', '—ŒŒ¡Ÿ≈Õ»≈');
define ('ADMIN_PASS', 'password');
define ('ADMIN_DISPLAY_LOGIN', 'No');
define ('DISPLAY_URL', 'Website');
define ('NOTIFY_SEND_MAIL', 'No' );
define ('NOTIFY_ADMIN_EMAIL', 'webmaster@yourwebsite.com');
define ('NOTIFY_ADMIN_SUBJECT', 'Guestbook New Post');
define ('NOTIFY_MAIL_BODY', 'Someone has signed your guestbook!');
define ('NOTIFY_INCLUDE_MSG', 'No');
define ('APPROVAL','Automatic');
define ('VERIFY_EMAIL_SUBJECT', 'Post Verification Required');
define ('VERIFY_EMAIL_BODY', "You have just signed our Guestbook.\nTo avoid spam, we perform email verification.\nTo activate your post, please click on the following link:\n");
define ('SUCCESS_REPORT','');
define ('SUCCESS_REPORT_VERIFY','');
define ('SUCCESS_REPORT_APPROVAL','');
define ('AUTH_SUCCESS','');
define ('DB_FILE','./guestbookdb.php');
define ('NO_POSTS','');
define ('ALLOW_URLS','Yes');
define ('ALLOW_BBCODE','Yes');
define ('DATEFORMAT','m/d/Y H:i:s');
define ('BANNED_DB_FILE','BANNED_'.DB_FILE);
define ('BANNED_MSG','');
define ('SHOW_FORM','Yes');
define ('MAX_MESSAGE_LENGTH',255);
define ('ITEMS_PER_PAGE',10);
define ('SIGN_GUESTBOOK','Sign the guestbook');
define ('NEXT_PAGE','Next Page');
define ('PREVIOUS_PAGE','Previous Page');
define ('ADMIN_LOGIN','Admin login');

$site = $_SERVER['HTTP_HOST'];
$site1 = substr($site, 4, strlen($site) - 4);
$script = $_SERVER['SCRIPT_NAME'];
$showform = isset($_GET['showform']) ? "Yes" : "No";

if (APPROVAL == 'Admin')
{
   define(REPORT_MESSAGE, SUCCESS_REPORT_APPROVAL);
}
else
if (APPROVAL == 'Email')
{
   define(REPORT_MESSAGE, SUCCESS_REPORT_VERIFY);
}
else
{
   define(REPORT_MESSAGE, SUCCESS_REPORT);
}
if ($_GET['report'] != "")
{
   define('REPORT', $_GET['report']);
}

if (!file_exists(DB_FILE))
{
   if ($file = fopen(DB_FILE, 'w'))
   {
      fclose($file);
   }
}

function strip_chars($var)
{
   return trim(str_replace("\r", NULL, htmlspecialchars(stripslashes(strip_tags($var)), ENT_QUOTES)));
}

function allowurls($var)
{
   $var = preg_replace('/http:\/\/[\w]+(.[\w]+)([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%%&\/~\+#])?/i', '<a href="$0" target="_blank">$0</a>', $var);
   return trim($var);
}

function bbcode($var)
{
   $var = preg_replace('(\[b\](.+?)\[\/b\])is', '<b>$1</b>', $var);
   $var = preg_replace('(\[i\](.+?)\[\/i\])is', '<i>$1</i>', $var);
   $var = preg_replace('(\[u\](.+?)\[\/u\])is', '<u>$1</u>', $var);
   return trim($var);
}

function htmlspecialchars_decode_ex($str)
{
   return strtr($str, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
}

if (isset($_POST['password']))
{
   $password = md5($_POST['password']);
   if ($password == md5(ADMIN_PASS))
   {
      setcookie('password', $password);
   }
}
else
{
   $password = isset($_COOKIE['password']) ? $_COOKIE['password'] : NULL;
}


$display = $_GET['display'];
if (isset($_GET['admin']) && $_GET['admin'] == 'logout')
{
   setcookie('password', '');
   unset($_COOKIE['password'], $password);
   header("Location: ".basename(__FILE__));
   exit;
}
else
if (isset($_GET['admin']) && ($_GET['admin'] == 'delete' || $_GET['admin'] == 'bandel'))
{
   if ($password == md5(ADMIN_PASS))
   {
      if ($file = fopen(DB_FILE, 'r'))
      {
         $data = '';
         while (!feof($file))
         {
            $line = fgets($file);
            list($timestamp) = explode(chr(02), trim($line));
            if ($timestamp == $_GET['msg'])
            {
               $data .= fread($file, filesize(DB_FILE));
               fclose($file);
               if (!$file = fopen(DB_FILE, 'w'))
               {
                  break;
               }
               fwrite($file, $data);
               fclose($file);
               break;
            }
            else
            {
               $data .= $line;
            }
         }
      }
   }
   header("Location: ".basename(__FILE__));
   exit;
}
else
if (isset($_GET['admin']) && ($_GET['admin'] == 'ban' || $_GET['admin'] == 'bandel'))
{
   if ($password == md5(ADMIN_PASS))
   {
      $email_to_ban = $_GET['email']."\n";
      if (file_exists(BANNED_DB_FILE))
      {
         $banned = file(BANNED_DB_FILE);
      }
      else
      {
         $banned = array();
      }
      for ($i = 0; $i < count($banned); $i++)
      {
	        $banned[$i] = trim($banned[$i]);
      }
      if (!in_array($email, $banned))
      {
         if ($file = fopen(BANNED_DB_FILE, 'a'))
         {
            fwrite($file, $email_to_ban);
            fclose($file);
         }
         header("Location: ".basename(__FILE__));
         exit;
      }
   }
}
else
if (isset($_GET['admin']) && $_GET['admin'] == 'approve')
{
   if ($password == md5(ADMIN_PASS))
   {
      if ($file = fopen(DB_FILE, 'r'))
      {
         $data = '';
         while (!feof($file))
         {
            $line = fgets($file);
            list($timestamp, $name, $subject, $email, $website, $message, $ip, $status, $hide_email, $check) = explode(chr(02), trim($line));
            if ($timestamp == $_GET['msg'])
            {
               $data .= $timestamp . chr(02) . $name . chr(02) . $subject . chr(02) . $email . chr(02) .  $website . chr(02) . $message . chr(02) . $ip . chr(02) ."Approved" . chr(02) . $hide_email . chr(02) . $check . "\n";
               $data .= fread($file, filesize(DB_FILE));
               fclose($file);
               if (!$file = fopen(DB_FILE, 'w'))
               {
                  break;
               }
               fwrite($file, $data);
               fclose($file);
               break;
            }
            else
            {
               $data .= $line;
            }
         }
      }
   }
   header("Location: ".basename(__FILE__));
   exit;
}

if (isset($_GET['check']) && isset($_GET['msg']))
{
   if ($file = fopen(DB_FILE, 'r'))
   {
      $data = '';
      while (!feof($file))
      {
         $line = fgets($file);
         list($timestamp, $name, $subject, $email, $website, $message, $ip, $status, $hide_email, $check) = explode(chr(02), trim($line));
         if ($timestamp == $_GET['msg'] && $check == $_GET['check'])
         {
            $data .= $timestamp . chr(02) . $name . chr(02) . $subject . chr(02) . $email . chr(02) .  $website . chr(02) . $message . chr(02) . $ip . chr(02) ."Approved" . chr(02) . $hide_email . chr(02) . $check . "\n";
            $data .= fread($file, filesize(DB_FILE));
            fclose($file);
            if (!$file = fopen(DB_FILE, 'w'))
            {
               echo "Could not open file for read <br>";
               break;
            }
            fwrite($file, $data);
            fclose($file);
            break;
         }
         else
         {
            $data .= $line;
         }
      }
   }
   header("Location: ".basename(__FILE__)."?report=".rawurlencode(AUTH_SUCCESS));
   exit;
}
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
   $_POST = str_replace(chr(02), ' ', $_POST);
   $ip = $_SERVER['REMOTE_ADDR'];
   $name = strip_chars($_POST['name']);
   $subject = strip_chars($_POST['subject']);
   $email = strip_chars($_POST['email']);
   $website = strip_chars($_POST['website']);
   $hide_email = strip_chars($_POST['hide_email']);
   $message = str_replace("\n", "<br>", strip_chars($_POST['message']));
   if (strlen($message) > MAX_MESSAGE_LENGTH)
   {
      $message = substr($message, MAX_MESSAGE_LENGTH);
   }
   $message1 = str_replace("<br>", "\n", $message);

   if (ALLOW_URLS == "Yes")
   {
      $message = allowurls($message);
   }

   if (ALLOW_BBCODE == "Yes")
   {
      $message = bbcode($message);
   }

   $timestamp = time();
   $status = "Approved";
   if (APPROVAL == "Email" || APPROVAL == "Admin")
   {
      $status = "Pending";
   }
   $check = md5($site.$timestamp.rand(100000, 999999));
   $verify_link = "http://".$site.$script."?msg=".$timestamp."&check=$check";
   if (file_exists(BANNED_DB_FILE))
   {
      $banned = file(BANNED_DB_FILE);
   }
   else
   {
      $banned = array();
   }
   for ($i = 0; $i < count($banned); $i++)
   {
      $banned[$i] = trim($banned[$i]);
   }
   if (!in_array($email, $banned))
   {
      if (!(empty($name) || empty($message)))
      {
         $file = fopen(DB_FILE, 'a+');
         $data = $timestamp . chr(02) . $name . chr(02) . $subject . chr(02) . $email . chr(02) .  $website . chr(02) . $message . chr(02) . $ip . chr(02) .$status . chr(02) . $hide_email . chr(02) . $check ." \n";
         fwrite($file, $data);
         fclose($file);
         $mailto  = NOTIFY_ADMIN_EMAIL;
         $subject = NOTIFY_ADMIN_SUBJECT;
         $header  = "From: Guestbook Post Notifier <". NOTIFY_ADMIN_EMAIL .">\r\n";
         $header .= "Reply-To: no_reply@".$site1."\r\n";
         $header .= "MIME-Version: 1.0"."\r\n";
         $header .= "Content-Type: text/plain; charset=utf-8"."\r\n";
         $header .= "Content-Transfer-Encoding: 8bit"."\r\n";
         $header .= "X-Mailer: PHP v".phpversion();
         $body = NOTIFY_MAIL_BODY."\n";

         if (NOTIFY_INCLUDE_MSG == "Yes")
         {
            $body .= htmlspecialchars_decode_ex($message1)."\n";
         }
         mail($mailto, $subject, $body, $header);
      }

      if (APPROVAL == "Email")
      {
         $mailto = $email;
         $subject = VERIFY_EMAIL_SUBJECT;
         $header  = "From: no_reply@$site1"."\r\n";
         $header .= "Reply-To: no_reply@".$site1."\r\n";
         $header .= "MIME-Version: 1.0"."\r\n";
         $header .= "Content-Type: text/plain; charset=utf-8"."\r\n";
         $header .= "Content-Transfer-Encoding: 8bit"."\r\n";
         $header .= "X-Mailer: PHP v".phpversion();
         $body    = htmlspecialchars_decode_ex(VERIFY_EMAIL_BODY) .$verify_link;
         mail($mailto, $subject, $body, $header);
      }
      header("Location: ".basename(__FILE__)."?report=".rawurlencode(REPORT_MESSAGE));
      exit;
   }
   else
   {
      header("Location: ".basename(__FILE__)."?report=".rawurlencode(BANNED_MSG));
   }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Œ“œ–¿¬»“‹ œŒ—“</title>
<style type="text/css">
body
{
   background-color: #FFFFFF;
   color: #666666;
}
td
{
   font-family: Tahoma;
   color: #666666;
   font-size: 11px;
}
input, textarea
{
   font-family: Tahoma;
   background-color: #FFFFFF;
   color: #666666;
   font-size: 11px;
   border-style: solid;
   border-width: 1px;
   border-color: #7F7F7F;
}
p.title
{
   font-family: Tahoma;
   font-size: 16px;
   color: #4D4D4D;
   font-weight: bold;
}
.banner
{
   font-family: Tahoma;
   font-size: 11px;
   background-color: #C0C0C0;
   color: #585858;
   font-weight: bold;
}
.message
{
   font-family: Tahoma;
   font-size: 11px;
   background-color: #E9E9E9;
   color: #666666;
}
.admin
{
   font-family: Tahoma;
   font-size: 11px;
   color: #4D4D4D;
}
.navigation
{
   font-family: Tahoma;
   font-size: 11px;
   color: #666666;
   font-weight: bold;
}
a:link, a:visited
{
   color: #4D4D4D;
}
a:hover
{
   color: #4D4D4D;
}
</style>
</head>
<body>
<?php
if (isset($_GET['admin']))
{
   if ($_GET['admin'] == 'login')
   {
      echo "<center>\r\n";
      echo "<p class=\"title\">Guestbook login</p>\r\n";
      echo "<form method=\"post\" action=\"".basename(__FILE__)."\"><p><input type=\"password\" name=\"password\" size=\"20\"> <input type=\"submit\" value=\"Login\" name=\"submit\"></p></form>\r\n";
      echo "</center>\r\n";
      exit;
   }
}
else
{
   echo "<p align=\"center\" class=\"title\">".TITLE."</p>\r\n";

   if ($password != md5(ADMIN_PASS))
   {
      if (SHOW_FORM == "Yes" || $showform == "Yes")
      {

         echo "<form action=\"".basename(__FILE__)."\" method=\"post\">\r\n";
         echo "<table width=\"100%\" style=\"background-color:#FFFFFF;\">\r\n";
         if (NAME != "")
         {
            echo "<tr>\r\n";
            echo "<td width=\"73px\">".NAME."</td>\r\n";
            echo "<td><input type=\"text\" value=\"\" name=\"name\" style=\"width:100%;\"></td>\r\n";
            echo "</tr>\r\n";
         }
         if (SUBJECT != "")
         {
            echo "<tr>\r\n";
            echo "<td>".SUBJECT."</td>\r\n";
            echo "<td><input type=\"text\" name=\"subject\" style=\"width:100%;\"></td>\r\n";
            echo "</tr>\r\n";
         }
         if (EMAIL != "")
         {
            echo "<tr>\r\n";
            echo "<td>".EMAIL."</td>\r\n";
            echo "<td><input type=\"text\" value=\"\" name=\"email\" style=\"width:100%;\"></td>\r\n";
            echo "</tr>\r\n";
         }
         if (WEBSITE != "")
         {
            echo "<tr>\r\n";
            echo "<td>".WEBSITE."</td>\r\n";
            echo "<td><input type=\"text\" value=\"\" name=\"website\" style=\"width:100%;\"></td>\r\n";
            echo "</tr>\r\n";
         }
         echo "<tr>\r\n";
         echo "<td valign=\"top\">".MESSAGE."</td>\r\n";
         echo "<td><textarea id=\"message\" name=\"message\" rows=\"3\" style=\"width:100%;\"></textarea></td>\r\n";
         echo "</tr>\r\n";
         echo "<tr>\r\n";
         echo "<td></td>\r\n";
         echo "<td><input type=\"submit\" name=\"Submit\" value=\"Œ“œ–¿¬»“‹\"/>";
         echo "</td>\r\n";
         echo "</tr>\r\n";
         echo "</table>\r\n";
         echo "</form>\r\n";
      }
   }

   if ($_GET['report']!= "")
   {
      echo "<div class=\"message\">".REPORT."</div>\r\n<br>\r\n";
   }

   if ($showform == "Yes")
   {
   }
   else
   if (filesize(DB_FILE) == 0)
   {
      echo "<div class=\"message\">".NO_POSTS."</div>\r\n<br>\r\n";
   }
   else
   {
      $items = file(DB_FILE);
      $items = array_reverse($items);
      str_replace("<", "&lt;", $items);
      str_replace(">", "&gt;", $items);
      str_replace("\n", "<br>\n", $items);
      $entry = 0;
      $item_count = 0;
      $total = count($items);
      if (isset($_GET['entry']))
      {
         $entry = $_GET['entry'];
      }
      $prev_entry = $entry - ITEMS_PER_PAGE;
      $next_entry = $entry + ITEMS_PER_PAGE;
      $items = array_slice($items, $entry);
      $navigation = '<span class="navigation" style="width:100%;text-align:right">';
      if ($prev_entry >= 0)
      {
         $navigation .= '<a href="'.basename(__FILE__).'?entry=';
         $navigation .= $prev_entry;
         $navigation .= '">'.PREVIOUS_PAGE.'</a>';
      }
      if ($next_entry < $total)
      {
         if ($prev_entry >= 0)
         {
            $navigation .= '&nbsp;&nbsp;';
         }
         $navigation .= '<a href="'.basename(__FILE__).'?entry=';
         $navigation .= $next_entry;
         $navigation .= '">'.NEXT_PAGE.'</a>';
      }
      $navigation .= "</span><br><br>\n";
      echo $navigation;
      foreach($items as $line)
      {
         $shown = false;
         list($timestamp, $name, $subject, $email, $website, $message, $ip, $status, $hide_email, $check) = explode(chr(02), trim($line));
         $topic = '<div class="banner">'.date(DATEFORMAT,$timestamp) . ' ';
         if ($email != "" && $hide_email != "Yes")
         {
            $topic .= '<a href="mailto:'.$email.'">';
         }
         $topic .= $name;
         if ($email != "" && $hide_email != "Yes")
         {
            $topic .= '</a>';
         }
         if ($website != "")
         {
            $website = str_replace("http://", "", $website);
            if (DISPLAY_URL == "")
            {
               define('DISPLAY_URL', $website);
            }
            $topic .= '&nbsp;&nbsp;&nbsp;<a href="http://'.$website.'" target="_blank">'.DISPLAY_URL.'</a>&nbsp;&nbsp;&nbsp; - ';
         }
         else
         {
            $topic .= " - ";
         }
         $topic .= $subject.'</div>';
         $topic .= '<div class="message">'.$message.'</div>';
         if (empty($password) && $status != "Pending")
         {
            echo $topic;
            echo "<br>\r\n";
            $shown = true;
         }
         else
         if ($password == md5(ADMIN_PASS))
         {
            if ($display != "for_approval" || ($display == "for_approval" && $status == "Pending"))
            {
               echo $topic;
               $shown = true;
            }
         }
         if ($password == md5(ADMIN_PASS) && $shown == true)
         {
            echo '<div class="admin">';
            if ($status == "Pending")
            {
               echo '<a href="'.basename(__FILE__).'?admin=approve&amp;msg='.$timestamp.'">[Approve]</a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            echo '<a href="'.basename(__FILE__).'?admin=delete&amp;msg='.$timestamp.'">[Delete]</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.basename(__FILE__).'?admin=ban&amp;msg='.$timestamp.'&amp;email='.rawurlencode($email).'">[Ban]</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.basename(__FILE__).'?admin=bandel&amp;msg='.$timestamp.'&amp;email='.rawurlencode($email).'">[Delete & Ban]</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://whois.sc/'.$ip.'" target="_blank" title="'.$ip.'">[IP whois]</a>&nbsp;&nbsp;&nbsp;&nbsp;';
            if ($website != "")
            {
               echo '<a href="http://whois.sc/'.$website.'" target="_blank" title="'.$ip.'">[Website whois]</a>&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            echo '</div><br>';
         }
         if ($shown == true)
         {
            $item_count++;
            if ($item_count >= ITEMS_PER_PAGE)
            {
               break;
            }
         }
      }
   }
   if ($showform == "Yes")
   {
      // do nothing
   }
   else
   if ($password == md5(ADMIN_PASS))
   {
      echo '<div class="admin">';
      echo '<a href="'.basename(__FILE__).'?admin=logout">Logout</a>&nbsp;&nbsp;&nbsp;&nbsp;';
      if ($display != "for_approval")
      {
         echo '<a href="'.basename(__FILE__).'?display=for_approval">Show posts for Approval</a>';
      }
      else
      {
         echo '<a href="'.basename(__FILE__).'">Show All posts</a>';
      }
      echo '</div>';
   }
   else
   {
      if (SHOW_FORM == "No")
      {
         echo '<span class="admin">';
         echo '<a href="'.basename(__FILE__).'?showform=yes">'.SIGN_GUESTBOOK.'</a>&nbsp;&nbsp;';
         echo '</span>';
      }
      if (ADMIN_DISPLAY_LOGIN == "Yes")
      {
         echo '<div class="admin">';
         echo '<a href="'.basename(__FILE__).'?admin=login">'.ADMIN_LOGIN.'</a>';
      }
      echo '</div>';
   }
}
?>
</body>
</html>
