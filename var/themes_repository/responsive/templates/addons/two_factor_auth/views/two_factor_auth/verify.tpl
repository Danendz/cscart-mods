{capture name="two_factor_auth"}
    <form name="two_factor_auth_form" action="{""|fn_url}" method="post" class="cm-ajax cm-ajax-full-render">
        <div class="ty-control-group">
            <label 
                for="verify_code" 
                class="ty-login__filed-label ty-control-group__label cm-trim"
                >
                {__("code")}
            </label>
            <input 
	            type="text" 
	            id="verify_code" 
	            name="verify_code" 
	            size="30" 
	            value="" 
	            class="login__input" 
            />
        </div>

        <div class="buttons-container clearfix">
            <div class="ty-float-right">
    		    {include
				    "buttons/button.tpl" 
				    but_name="dispatch[two_factor_auth.send_code]" 
				    but_text=__("two_factor_auth_send_code") 
				    but_role="act" 
				    but_meta="ty-btn__primary ty-btn__big ty-btn"
                }

    		    {include
				    "buttons/button.tpl" 
				    but_name="dispatch[two_factor_auth.confirm_code]" 
				    but_text=__("two_factor_auth_confirm_code") 
				    but_role="submit" 
				    but_meta="ty-btn__primary ty-btn__big ty-btn"
                }
            </div>
        </div>
    </form>
{/capture}

<div class="two-factor-auth ty-login">
    {$smarty.capture.two_factor_auth nofilter}
</div>
{capture name="mainbox_title"}{__("two_factor_auth_verify")}{/capture}
