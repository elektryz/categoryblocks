{if !isset($categoryblocks_hide)}
    {if $categoryblocks_first_products|count > 0 && $categoryblocks_second_products|count > 0}
        <div id="categoryblocks-module">
            <h2>{l s="Product categories" mod='categoryblocks'}</h2>
            <div class="categoryblocks-container">
                <div class="categoryblock">
                    <h3>{$categoryblocks_first_name}</h3>

                    <div class="categoryblock-inner">
                    {foreach from=$categoryblocks_first_products item=product_info}
                        {include file=$categoryblocks_productbox product_info=$product_info}
                    {/foreach}
                    </div>
                    <div class="text-center">
                    <a class="btn btn-more" href="{$categoryblocks_first_url}">
                        {l s="See more products from this category" mod='categoryblocks'}
                    </a>
                    </div>
                </div>

                <div class="categoryblock">
                    <h3>{$categoryblocks_second_name}</h3>
                    <div class="categoryblock-inner">
                    {foreach from=$categoryblocks_second_products item=product_info}
                        {include file=$categoryblocks_productbox product_info=$product_info}
                    {/foreach}
                    </div>

                    <div class="text-center">
                    <a class="btn btn-more" href="{$categoryblocks_second_url}">
                        {l s="See more products from this category" mod='categoryblocks'}
                    </a>
                    </div>
                </div>
            </div>

        </div>
    {/if}
{/if}