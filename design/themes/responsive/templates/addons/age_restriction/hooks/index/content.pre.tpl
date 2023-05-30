{if $is_legit === "Addons\\AgeRestriction\\AgeRestrictionTypes::DENIED"|enum}
	{include 
    "addons/age_restriction/views/age_restriction/components/restrict_access.tpl"
    }
{elseif $is_legit === "Addons\\AgeRestriction\\AgeRestrictionTypes::NOT_SET"|enum}
	{include 
    "addons/age_restriction/views/age_restriction/components/confirm_age.tpl"
    }
{/if}