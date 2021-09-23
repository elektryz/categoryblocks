<div class="productbox">
    <form method="POST" action="{$categoryblocks_cart_url}" class="add-to-cart-or-refresh">
    <div class="special-flex-container">
        <img src="{$product_info.cover}"> <a href="{$product_info.url}" target="_blank">{$product_info.name}</a>
        <div class="qty-changers">
            <input type="number" value="1" class="qty-input" min="1" step="1">
            <button class="btn add-to-cart" data-button-action="add-to-cart" type="submit">
                <i class="material-icons plus"></i></button>
           </div>
    </div>
        <input type="hidden" name="token" value="{$categoryblocks_token}">
        <input type="hidden" name="id_product" value="{$product_info.id_product}" class="product_page_product_id">
        <input type="hidden" name="qty" value="1">
    </form>
</div>
