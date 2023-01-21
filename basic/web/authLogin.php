<!-- msal.min.js can be used in the place of msal.js; included msal.js to make debug easy -->
<script type="text/javascript" src="https://alcdn.msauth.net/lib/1.4.4/js/msal.js"
    integrity="sha384-fTmwCjhRA6zShZq8Ow5ZkbWwmgp8En46qW6yWpNEkp37MkV50I/V2wjzlEkQ8eWD" crossorigin="anonymous">
</script>

<script src="https://code.jquery.com/jquery-3.6.1.min.js"
    integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>

<!-- msal.js with a fallback to backup CDN -->
<script type="text/javascript">
if (typeof Msal === 'undefined') document.write(unescape(
    "%3Cscript src='https://alcdn.msftauth.net/lib/1.4.4/js/msal.js' type='text/javascript' %3E%3C/script%3E"));
</script>

<script src="js\microsoftOAuth.js"></script>

<!--<div>
    <h1>Login successfully</h1>
    <a href="/synvm/basic/web/index.php?r=general-exception-issues/index">Go to Home</a>
</div>-->