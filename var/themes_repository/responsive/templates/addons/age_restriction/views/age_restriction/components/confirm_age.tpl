<div 
    class="cm-dialog-auto-open cm-dialog-auto-size" 
    id="confirm-age-dialog"
    title="{__("age_restriction_confirm_age")}" 
>
	<form action="{""|fn_url}"
	 method="post" 
	 name="confirm_age_form">
		<input type="hidden" name="redirect_url" value="{$config.current_url}" />
		<input 
			class="unfocus-hack cm-external-focus" 
			data-ca-external-focus-id="unfocus-hack"
			type="text" 
		>

		<div class="ty-control-group">
    	    <label 
            class="ty-control-group__title cm-required" 
            for="elm_confirm_age">{__("age_restriction_birthday")}</label>
			{include 
				"common/calendar.tpl"
                date_id="elm_confirm_age" 
                date_name="age" 
                date_val=$smarty.const.TIME 
			}
            <p>
                <small>{__("age_restriction_min_age_to_access")}: {$min_age}</small>
            </p>
		</div>

		<div class="buttons-container">			
    		{include
				"buttons/button.tpl" 
				but_name="dispatch[age_restriction.verify]" 
				but_text=__("submit") 
				but_role="submit" 
				but_meta="ty-btn__primary ty-btn__big ty-btn"
            }
		</div>
	</form>
</div>