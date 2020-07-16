<?php


namespace User\Service;


use Patreon\API;
use Patreon\OAuth;

class PatreonManager
{
    private $client_id = "_95TC9mhSPNvKnEMirH3N4LMDthwKL1dvRoVzvoY6eaFEU5qLxkCINJ_Kbejvgs1";
    private $client_secret = "o34EtbIBaPcstx9iWfrkfbGo7-ZBVn0PiUjDiqYEykVhKVbOJf63SnsBcpg3UfIO";
    private $redirect_uri = "https://www.drafttradewin.com/login";
    private $oauth_client;

    public function __construct()
    {
        $this->oauth_client = new OAuth($this->client_id, $this->client_secret);
    }

    public function getLoginButton()
    {
        $href = 'https://www.patreon.com/oauth2/authorize?response_type=code&client_id='.$this->client_id.'&redirect_uri='.urlencode($this->redirect_uri);
        // You can send an array of vars to Patreon and receive them back as they are. Ie, state vars to set the user state, app state or any other info which should be sent back and forth.
        // for example lets set final page which the user needs to land at - this may be a content the user is unlocking via oauth, or a welcome/thank you page
        // Lets make it a thank you page
        $state = array();
        $state['final_page'] = 'http://mydomain.com/thank_you';
        // Prepare state var. It must be json_encoded, base64_encoded and url encoded to be safe in regard to any odd chars
        $state_parameters = '&state=' . urlencode( base64_encode(json_encode($state)));
        // Append it to the url
        $href .= $state_parameters;
        $scope_parameters = '&scope=identity%20identity'.urlencode('[email]');
        $href .= $scope_parameters;

        return $href;
    }

    public function getSignUpButton()
    {
        $redirect_uri = "http://mydomain.com/patreon_login";
        // Min cents is the amount in cents that you locked your content or feature with. Say, if a feature or content requires $5 to access in your site/app, then you send 500 as min cents variable. Patreon will ask the user to pledge $5 or more.
        $min_cents = '500';
        // Scopes! You must request the scopes you need to have the access token.
        // In this case, we are requesting the user's identity (basic user info), user's email
        // For example, if you do not request email scope while logging the user in, later you wont be able to get user's email via /identity endpoint when fetching the user details
        // You can only have access to data identified with the scopes you asked. Read more at https://docs.patreon.com/#scopes
        // Lets request identity of the user, and email.
        $scope_parameters = '&scope=identity%20identity'.urlencode('[email]');
        // Generate the unified flow url - this is different from oAuth login url. oAuth login url just processes oAuth login.
        // Unified flow will do everything.
        $href = 'https://www.patreon.com/oauth2/become-patron?response_type=code&min_cents=' . $min_cents . '&client_id=' . $this->client_id . $scope_parameters . '&redirect_uri=' . $redirect_uri;
        // You can send an array of vars to Patreon and receive them back as they are. Ie, state vars to set the user state, app state or any other info which should be sent back and forth.
        $state = array();
        $state['final_redirect'] = 'http://mydomain.com/locked-content';
        // Or, http://mydomain.com/premium-feature. Or any url at which a locked feature or content will be unlocked after the user is verified to become a qualifying member
        // Add any number of vars you need to this array by $state['key'] = variable value
        // Prepare state var. It must be json_encoded, base64_encoded and url encoded to be safe in regard to any odd chars. When you receive it back, decode it in reverse of the below order - urldecode, base64_decode, json_decode (as array)
        $state_parameters = '&state=' . urlencode( base64_encode( json_encode( $state ) ) );
        // Append it to the url
        $href .= $state_parameters;

        return $href;
    }

    public function getTokens($code)
    {
        $tokens = $this->oauth_client->get_tokens($code, $this->redirect_uri);
        return $tokens;
    }

    public function getPatreonInfo($accessToken)
    {
        $api_client = new API($accessToken);
        // Fetch the user's details
        $current_member = $api_client->fetch_user();
        return $current_member;
    }
}