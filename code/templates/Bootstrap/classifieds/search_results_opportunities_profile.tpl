{include file="investor_profile_header.tpl"}
<div class="container details-body details-body__company-profile">
    <div class="row">
        {include file="investor_profile.tpl"}
        <div class="col-xs-12 details-body__left companies-jobs-list">
            <div class="search-results__top clearfix">
                <h3 class="search-results__title">
                    {assign var="opps_number" value=$listing_search.listings_number}
                    {assign var="company_name" value=$userInfo.CompanyName|escape}
                    [[$opps_number opportunities at $company_name]]
                </h3>
            </div>
            {if $listings}
                <div class="search-results listing">
                    {include file="search_results_opportunities_listings.tpl"}
                    <button type="button" class="load-more btn btn__white {if $listing_search.listings_number <= $listing_search.listings_per_page}hidden{/if}" data-backfilling="false" data-page="2">
                        [[Load more]]
                    </button>
                </div>
            {/if}
        </div>
    </div>
</div>