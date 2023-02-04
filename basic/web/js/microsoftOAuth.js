var dynamicUrl = `${window.location.origin}/${
  window.location.pathname.split("/")[1]
}/${window.location.pathname.split("/")[2]}/web/authLogin.php`;

var localhostUrl = `${window.location.origin}/${
  window.location.pathname.split("/")[1]
}/web/authLogin.php`;

var origin = window.location.origin;

const msalConfig = {
  auth: {
    clientId: "c89d429a-f009-4fd7-bf29-fb8773c53b57",
    authority: "https://login.microsoftonline.com/common/",
    redirectUri: origin === "http://localhost" ? localhostUrl : dynamicUrl,
  },
  cache: {
    cacheLocation: "sessionStorage", // This configures where your cache will be stored
    storeAuthStateInCookie: false, // Set this to "true" if you are having issues on IE11 or Edge
  },
};

var loginRequest = {
  scopes: ["openid", "profile", "User.Read"],
};

var tokenRequest = {
  scopes: ["User.Read"],
};

// Create the main myMSALObj instance
// configuration parameters are located at authConfig.js
const myMSALObj = new Msal.UserAgentApplication(msalConfig);

let accessToken;

// Register Callbacks for Redirect flow
// myMSALObj.handleRedirectCallback(authPopupCallBack);

// Helper function to call MS Graph API endpoint
// using authorization bearer token scheme
//function callMSGraph(endpoint, token, callback) {
//  const headers = new Headers();
//  const bearer = `Bearer ${token}`;
//
//  headers.append("Authorization", bearer);
//
//  const options = {
//    method: "GET",
//    headers: headers,
//  };
//
//  console.log("request made to Graph API at: " + new Date().toString());
//
//  fetch(endpoint, options)
//    .then((response) => response.json())
//    .then((response) => callback(response, endpoint))
//    .catch((error) => console.log(error));
//}

async function signIn() {
  myMSALObj
    .loginPopup(loginRequest)
    .then(async (loginResponse) => {
      console.log("id_token acquired at: " + new Date().toString());
      console.log(loginResponse);

      if (myMSALObj.getAccount()) {
        let token = await getTokenPopup(loginRequest);
        console.log("token", token);
         updateUI(token.accessToken, "https://graph.microsoft.com/v1.0/me")
      }
    })
    .catch((error) => {
      console.log(error);
    });
}

function signOut() {
  myMSALObj.logout();
}

// This function can be removed if you do not need to support IE
function getTokenPopup(request) {
  return myMSALObj.acquireTokenSilent(request).catch((error) => {
    console.log(error);
    console.log("silent token acquisition fails. acquiring token using popup");

    // fallback to interaction when silent call fails
    return myMSALObj
      .acquireTokenPopup(request)
      .then((tokenResponse) => {
        return tokenResponse;
      })
      .catch((error) => {
        console.log(error);
      });
  });
}

async function updateUI(data, endpoint) {
 
  const MStoken = await JSON.stringify(data);
  if (endpoint === "https://graph.microsoft.com/v1.0/me") {
    $.ajax({
      url: `<?php echo yii\helpers\Url::toRoute('/site/sub-domain-m-s-login'); ?>`,
      data: { MStoken },
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      success: function (result) {
        console.log(result);
      },
    });
  }
}