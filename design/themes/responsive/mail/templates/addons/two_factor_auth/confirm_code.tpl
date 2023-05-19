{include file="common/letter_header.tpl"}

{__("hello")},<br /><br />

{__("two_factor_auth_text_confirm_code")}
<br />
<br />
{__("code")}: <b>{$email_code}</b>

{include file="common/letter_footer.tpl"}