<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('max_execution_time', 300); //300 seconds = 5 minutes. In case if your CURL is slow and is loading too much (Can be IPv6 problem)

error_reporting(E_ALL);

define('OAUTH2_CLIENT_ID', '');
define('OAUTH2_CLIENT_SECRET', '');
define('OAUTH2_REDIRECT_URI', '');
define('INBOXGUILDID', );

$authorizeURL = 'https://discord.com/api/oauth2/authorize';
$tokenURL = 'https://discord.com/api/oauth2/token';
$apiURLBase = 'https://discord.com/api/users/@me';
$getGuild = 'https://discord.com/api/users/@me/guilds';

session_start();

// Start the login process by sending the user to Discord's authorization page
if(get('action') == 'login') {

  $params = array(
    'client_id' => OAUTH2_CLIENT_ID,
    'redirect_uri' => OAUTH2_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'identify guilds'
  );

  // Redirect the user to Discord's authorization page
  header('Location: https://discordapp.com/api/oauth2/authorize' . '?' . http_build_query($params));
  die();
}


// When Discord redirects the user back here, there will be a "code" and "state" parameter in the query string
if(get('code')) {

  // Exchange the auth code for a token
  $token = apiRequest($tokenURL, array(
    "grant_type" => "authorization_code",
    'client_id' => OAUTH2_CLIENT_ID,
    'client_secret' => OAUTH2_CLIENT_SECRET,
    'redirect_uri' => OAUTH2_REDIRECT_URI,
    'code' => get('code')
  ));
  $logout_token = $token->access_token;
  $_SESSION['access_token'] = $token->access_token;

  if (isset($_SESSION['requested_url']))
  {
    header("Location: /modmail/" . $_SESSION['requested_url']);
  }
  else
  {
    header('Location: ' . $_SERVER['PHP_SELF']);
  }
}

if(session('access_token')) {
  $user = apiRequest($apiURLBase);
  echo 'Logged in as '.$user->username.'&nbsp;&nbsp;';
  echo '<a href="/modmail/passthrough.php?action=logout"/>Log out?</a><br /><br />';

  if (is_authed(INBOXGUILDID))
  {
    if (get('modmail_id', '') != '')
    {
      echo getModmail("http://127.0.0.1:7145/" . get('modmail_id'));
    }
  }
  else
  {
    echo "You can't see this!";
    echo '<a href="/modmail/passthrough.php?action=logout"/>Log out?</a>';
  }

} else {
  if (get('modmail_id', '') == '')
  {
    echo 'You need to ask for a modmail message you dingus.';
  }
  else
  {
    $_SESSION['requested_url'] = get('modmail_id', '');
    echo '<meta http-equiv="refresh" content="0;URL=\'/modmail/passthrough.php?action=login\'" />';
  }
}


if(get('action') == 'logout') {
  // Redirect the user to Discord's revoke page
  session_destroy();
  header('Location: /modmail/passthrough.php');
  die();
}

function getModmail($url) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

  $response = curl_exec($ch);

  $response = curl_exec($ch);
  return $response;
}


function apiRequest($url, $post=FALSE, $headers=array()) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

  $response = curl_exec($ch);


  if($post)
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

  $headers[] = 'Accept: application/json';

  if(session('access_token'))
  {
    $headers[] = 'Authorization: Bearer ' . session('access_token');
  }

  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $response = curl_exec($ch);
  return json_decode($response);
}

function get($key, $default=NULL) {
  return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
}

function session($key, $default=NULL) {
  return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
}

function is_authed($guild_id)
{
  global $getGuild;
  if (isset($_SESSION['guildid']) and $_SESSION['guildid'] == $guild_id)
  {
    return true;
  }
  $userGuilds = apiRequest($getGuild);
  foreach ($userGuilds as $guild)
  {
    if ($guild->id == $guild_id)
    {
        echo "Validated";
        $_SESSION['guildid'] = $guild_id;
        return true;
    }
  }
  unset($_SESSION['guildid']);
  return false;
}

?>
