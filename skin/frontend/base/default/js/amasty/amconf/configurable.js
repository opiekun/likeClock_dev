var addParamsToHash = false;

AmConfigurableData = Class.create();
AmConfigurableData.prototype =
{
    textNotAvailable : "",
    optionProducts : null,
    optionDefault : [],
    productImageSelector: 'div.product-image img',
    productGallerySelector: '#amasty_gallery a img',
    cachedImages: [],

    initialize : function(optionProducts)
    {
        this.optionProducts = optionProducts;
        if ('undefined' != typeof(window.amastyStwatchesFullSelect)
            && Product.Config.prototype.fillSelect.toString().indexOf('swatch-select') > 0
        ) {
            Product.Config.prototype.fillSelect = window.amastyStwatchesFullSelect;
        }
    },

    reloadOptions : function() //special for simple price
    {
        if ('undefined' != typeof(spConfig) && spConfig.settings)
        {
            spConfig.settings.each(function(select){
                if (select.enable) {
                    spConfig.reloadOptionLabels(select);
                }
            });
        }
    },

    hasKey : function(key)
    {
        return ('undefined' != typeof(this.optionProducts[key]));
    },

    getData : function(key, param)
    {
        if (this.hasKey(key) && 'undefined' != typeof(this.optionProducts[key][param]))
        {
            return this.optionProducts[key][param];
        }
        return false;
    },

    saveDefault : function(param, data)
    {
        this.optionDefault['set'] = true;
        this.optionDefault[param] = data;
    },

    getDefault : function(param)
    {
        if ('undefined' != typeof(this.optionDefault[param]))
        {
            return this.optionDefault[param];
        }
        return false;
    }
};

Product.Config.prototype.amOrig_resetChildren = Product.Config.prototype.resetChildren;
Product.Config.prototype.resetChildren = function(element){
    this.amOrig_resetChildren(element);
    if('undefined' != typeof(Product.ConfigurableSwatches)) {
        if (element.childSettings) {
            for (var i = 0; i < element.childSettings.length; i++) {
                element.childSettings[i].selectedIndex = 0;
                element.childSettings[i].disabled = true;
                if (element.config) {
                    this.state[element.config.id] = false;
                }
            }
        }
    }

    /* remove old option label */
    var parent = element.parentNode.parentNode.previousElementSibling;
    if ( typeof(parent) != 'undefined'
        && parent != null && parent.nodeName == "DT"
        && (conteiner = parent.select("label")[0])
        && !element.value
    ) {
        if (tmp = conteiner.select('span.amconf-label')[0]) {
            tmp.innerHTML = '';
        }
    }

    if (!confData.optionProducts.showAllOptions) {
        this.processEmpty();
    }
};

Product.Config.prototype.amOrigConfigureForValues = Product.Config.prototype.configureForValues;
Product.Config.prototype.configureForValues = function(element){
    if (!this.configureForValuesCompleted && confData && confData.optionProducts.showAllOptions === '1') {
        for (var i=this.settings.length-1;i>=0;i--) {
            if (this.settings[i].disabled === true) {
                this.fillSelect(this.settings[i], true);
            }
        }

        this.configureForValuesCompleted = true;
    }
    this.amOrigConfigureForValues(element);
};

Product.Config.prototype.amconfCreateOptionImage = function(option, attributeId, isActive){
    if (!this.config.attributes[attributeId].use_image) {
        return '';
    }

    var key = '';
    this.settings.each(function(select, ch){
        if (parseInt(select.value)) {
            key += select.value + ',';
        }
    });

    var imgContainer = new Element('div', {
        'class': 'amconf-image-container',
        'id'   : 'amconf-images-container-' + attributeId
    });

    var width  = parseInt(this.config.attributes[attributeId].config.small_width)
        ? parseInt(this.config.attributes[attributeId].config.small_width): 50;
    var height = parseInt(this.config.attributes[attributeId].config.small_height)
        ? parseInt(this.config.attributes[attributeId].config.small_height): 50;
    var useTooltip = this.config.attributes[attributeId].config
        && this.config.attributes[attributeId].config.use_tooltip != "0"
        && 'undefined' != typeof(jQuery);

    if (option.color || !option.image) {
        var div = new Element('div', {
            'class': 'amconf-color-container',
            'id'   : 'amconf-image-' + option.id
        });
        div.setStyle({
            width: width + 'px',
            height: height + 'px'
        });

        if(option.color){
            div.setStyle({background: '#' + option.color});
        }
        else{
            div.setStyle({lineHeight: height + 'px'});
            div.addClassName('amconf-noimage-div');
            div.insert(option.label);
        }
        imgContainer.appendChild(div);
        if (isActive > 0) {
            div.observe('click', this.configureImage.bind(this));
        } else {
            div.addClassName('am-disabled');
        }

        if(useTooltip){
            amcontentPart = 'background: #' + option.color + '">';
        }
    }
    else {
        var div = new Element('img', {
            'src'   :  option.image,
            'class' :  "amconf-image",
            'id'    : 'amconf-image-' + option.id,
            'alt'   : option.label,
            'title' : option.label,
            'width' : width,
            'height': height
        });
        if (isActive > 0) {
            div.observe('click', this.configureImage.bind(this));
        } else {
            div.addClassName('am-disabled');
        }
        imgContainer.appendChild(div);

        if(useTooltip){
            amcontentPart = '"><img src="' + option.bigimage + '"/>';
        }
    }

    /*Add tooltip*/
    if(useTooltip){
        var tooltipWidth  = parseInt(this.config.attributes[attributeId].config.big_width) ? parseInt(this.config.attributes[attributeId].config.big_width) : 100;
        var tooltipHeight = parseInt(this.config.attributes[attributeId].config.big_height)? parseInt(this.config.attributes[attributeId].config.big_height): 100;
        switch (this.config.attributes[attributeId].config.use_tooltip) {
            case "1":
                amcontent = '<div class="amtooltip-label">' + option.label + '</div>';
                break;
            case "2":
                amcontent = '<div class="amtooltip-img" style="width: ' + tooltipWidth + 'px; height:' + tooltipHeight + 'px; margin: 0 auto;' + amcontentPart + '</div>';
                break;
            case "3":
                amcontent = '<div class="amtooltip-img" style="width: ' + tooltipWidth + 'px; height:' + tooltipHeight + 'px; margin: 0 auto;' + amcontentPart + '</div>' +
                            '<div class="amtooltip-label">' +
                                option.label +
                            '</div>';
                break;
        }
        try{
            jQuery(div).tooltipster({
                content: jQuery(amcontent),
                theme: 'tooltipster-light',
                animation: 'grow',
                touchDevices: false,
                position: "top"
            });
        }
        catch(exc){
            console.debug(exc);
        }
    }

    /*Add out of stock cross line*/
    if( key.indexOf("," + option.id + ",") > 0 ){
        var keyOpt = key.substr(0, key.length - 1);
    }
    else{
        var keyOpt = key +  option.id;
    }
    if(typeof confData != 'undefined' && confData.getData(keyOpt, 'not_is_in_stock')){
        var hr = new Element('hr', {
            'class'  : 'amconf-hr',
            'size'   : 4,
            'noshade'   : 'noshade'
        });
        div.addClassName('amconf-image-outofstock');

        var angle  = Math.atan(height/width);
        hr.setStyle({
            width     : Math.sqrt(width*width + height*height) + 1 + 'px',
            top       : height/2  + 'px',
            left      : -(Math.sqrt(width*width + height*height) - width)/2  + 'px',
            transform : "rotate(" + Math.floor(180-angle * 180/ Math.PI)+ "deg)"
        });

        imgContainer.appendChild(hr);
        hr.observe('click', this.configureHr.bind(this));
    }

    /*Add titles under image*/
    if(this.config.attributes[attributeId].config && this.config.attributes[attributeId].config.use_title != "0"){
        var amImgTitle = new Element('div', {
            'class': 'amconf-image-title',
            'id'   : 'amconf-images-title-' + option.id
        });
        amImgTitle.setStyle({
            fontWeight : 600,
            textAlign : 'center',
            paddingTop: '7px'
        });

        var str = option.label;
        if ('undefined' != typeof(confData.optionProducts.swatchesPrice)
            && confData.optionProducts.swatchesPrice == '1'
            && confData['optionProducts'][keyOpt]
            && confData['optionProducts'][keyOpt]['price']
        ) {
            str = '<p class="amconf-price-label">' +
                        this.formatPrice(confData['optionProducts'][keyOpt]['price'], true) +
                   '</p>' + str;
            str = str.replace("+", "");
        }
        amImgTitle.innerHTML = str;

        imgContainer.appendChild(amImgTitle);
    }

    return imgContainer;
};

Product.Config.prototype.fillSelect = function(element, disabled){
    var attributeId = element.id.replace(/[a-z]*/, '');
    var options     = this.getAttributeOptions(attributeId);
    var savedValue  = element.value;
	this.clearSelect(element);
    element.options[0] = new Option(this.config.chooseText, '');

    if('undefined' !== typeof(AmTooltipsterObject)) {
        AmTooltipsterObject.load();
    }

    var prevConfig = false;
    if(element.prevSetting){
        prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
    }

    if(options) {
        var existImages = $('amconf-images-' + attributeId);
        if (existImages) {
            existImages.remove();
        }

        if (typeof amprmatrix !== 'undefined' && element === $$('.super-attribute-select::last').first()) {
            var table = this.prepareForMatrix(element, attributeId);
            confData.optionProducts.showAllOptions = '0';
        }

        if (this.config.attributes[attributeId].use_image) {
            holder = element.parentNode;
            window.holderDiv = new Element('div', {
                'class': 'amconf-images-container',
                'id'   : 'amconf-images-' + attributeId
            });

            holder.insertBefore(holderDiv, element);
        }

        var index = 1;
        for(var i=0;i<options.length;i++){
            var allowedProducts = [];
            if(prevConfig) {
                for(var j=0;j<options[i].products.length;j++){
                    if(prevConfig.config && prevConfig.config.allowedProducts
                        && prevConfig.config.allowedProducts.indexOf(options[i].products[j])>-1){
                        allowedProducts.push(options[i].products[j]);
                    }
                }
            }
            else {
                allowedProducts = options[i].products.clone();
            }

            if(allowedProducts.size() > 0 || confData.optionProducts.showAllOptions === '1')
            {
                options[i].allowedProducts = allowedProducts;
                var newOption = new Option(this.getOptionLabel(options[i], options[i].price), options[i].id);
                newOption.disabled = !(allowedProducts.size() > 0);
                element.options[index] = newOption;
                element.options[index].config = options[i];
                index++;

                if (typeof amprmatrix !== 'undefined' && element === $$('.super-attribute-select::last').first()) {
                    this.createOptionRow(options[i], attributeId, allowedProducts.size() > 0, table);
                } else {
                    var imgContainer = this.amconfCreateOptionImage(options[i], attributeId, allowedProducts.size());
                    if (imgContainer) {
                        holderDiv.appendChild(imgContainer);
                    }
                }
            }
        }
        if(this.config.attributes[attributeId].use_image) {
            /*add slider for swatches*/
            if(this.config.attributes[attributeId].enable_carousel == "1"){
                $holderDiv = jQuery(holderDiv);
                var imageWidth = parseInt(this.config.attributes[attributeId].config.small_width)  ? parseInt(this.config.attributes[attributeId].config.small_width): 50;
                holderDiv.childElements().each(function(item){
                    if(jQuery(item).width() > imageWidth){
                        imageWidth = jQuery(item).width();
                    }
                });
                var count = Math.floor(($holderDiv.width() - 50) / (imageWidth + 5) );
                var visibleItems = count > 0? count: 3;
                $holderDiv.parent().children('.caroufredsel_wrapper, .am-swatch-arrow').remove();

                if( $holderDiv.children().length > visibleItems){
                    var carouHeight = parseInt(this.config.attributes[attributeId].config.small_height)  ? parseInt(this.config.attributes[attributeId].config.small_height): 50;

                    $holderDiv.children().css('margin', '6px 3px 9px 3px');
                    carouHeight += 15;

                    var nextLink = jQuery('<div class="am-swatch-next am-swatch-arrow"></div>');
                    $holderDiv.parent().append(nextLink);

                    var prevLink = jQuery('<div class="am-swatch-prev am-swatch-arrow"></div>');
                    $holderDiv.parent().append(prevLink);
                    $holderDiv.parent().children('.am-swatch-arrow').css('top', carouHeight/2 - 12);

                    AmcarouFredSelObject.load();
                    $holderDiv.carouFredSel({
                        circular: false,
                        infinite: false,
                        items: {
                            visible : visibleItems,
                            minimum : visibleItems
                        },
                        scroll: {
                            items       : 1,
                            fx          : 'directscroll',
                            duration    : 700,
                            pauseOnHover: true
                        },
                        auto: {
                            play: false
                        },
                        prev: {
                            button: prevLink
                        },
                        next: {
                            button: nextLink
                        }
                    });
                }
            }

        }
        /*save value from previous step*/
        if(parseInt(savedValue)
            && this.settings.length <= 2
            && element.select('[value="' + savedValue + '"]').length
            && confData.optionProducts.showAllOptions !== '1'
        ){
            element.value = savedValue;
            this.configureElement(element);
        }
    }
};

window.amastyStwatchesFullSelect = Product.Config.prototype.fillSelect;

Product.Config.prototype.configureElement = function(element)
{
    var optionId = element.value;
    var imageBlock = $('amconf-image-' + optionId);

    if (imageBlock) {
        if (imageBlock.hasClassName('am-disabled')) {
            return;
        }
        this.selectImage(imageBlock);
    } else {
        attributeId = element.id.replace(/[a-z-]*/, '');
        if ($('amconf-images-' + attributeId))
        {
            $('amconf-images-' + attributeId).childElements().each(function(child){
                 child.childElements().each(function(children){
                    children.removeClassName('amconf-image-selected');
                 });
            });
        }
    }
    // extension Code End

    this.reloadOptionLabels(element);
    if(element.value){
        this.state[element.config.id] = element.value;
        if (confData.optionProducts.showAllOptions) {
            var currentSetting = element.nextSetting;
            while (element.nextSetting) {
                element.nextSetting.disabled = false;
                if (element.value) {
                    element.setAttribute('prev-option', element.value);
                }
                this.fillSelect(element.nextSetting);
                this.resetChildren(element.nextSetting);
                element = element.nextSetting;
            }
            if (element.value) {
                element.setAttribute('prev-option', element.value);
            }
            while (currentSetting) {
                if (currentSetting.getAttribute('prev-option')) {
                    currentSetting.value = currentSetting.getAttribute('prev-option');
                    this.configureElement(currentSetting);
                }
                currentSetting = currentSetting.nextSetting;
            }
        } else if(element.nextSetting){
            element.nextSetting.disabled = false;
            this.fillSelect(element.nextSetting);
            this.resetChildren(element.nextSetting);
        }
    }
    else {
        // extension Code
        if(element.childSettings) {
            for(var i=0;i<element.childSettings.length;i++){
                attributeId = element.childSettings[i].id.replace(/[a-z-]*/, '');
                jQuery('#attribute' + attributeId).parent('.input-box').children('.caroufredsel_wrapper, .am-swatch-arrow').remove();
                var amconfImages = $('amconf-images-' + attributeId);
                if (amconfImages)
                {
                    amconfImages.parentNode.removeChild(amconfImages);
                }
            }
        }
        // extension Code End

        this.resetChildren(element);

        // extension Code
        if (this.settings[0].hasClassName('no-display') && !confData.optionProducts.showAllOptions)
        {
            this.processEmpty();
        }
        // extension Code End
    }

    var key = '';
    var stock = 1;
    this.settings.each(function(select, ch){
        // will check if we need to reload product information when the first attribute selected
        if (parseInt(select.value))
	    {
            key += select.value + ',';
            if(confData.getData(key.substr(0, key.length - 1), 'not_is_in_stock')) {
               stock = 0;
            }
        }
    });
    key = key.substr(0, key.length - 1);
    this.updateData(key);

    //<---- Price update start---->
    if (typeof confData !== 'undefined' && confData.optionProducts.useSimplePrice == 1) {
        // replace price values with the selected simple product price
        this.reloadSimplePrice(key);
    } else {
        // default behaviour
        this.reloadPrice();
    }
    //<---- Price update end---->

    if (stock === 0) {
        $$('.add-to-cart').each(function(elem) {
            elem.hide();
        });
    } else {
        $$('.add-to-cart').each(function(elem) {
            elem.show();
        });
    }
    // for compatibility with custom stock status extension:
    if ('undefined' != typeof(stStatus) && 'function' == typeof(stStatus.onConfigure))
    {
	    var keySt = '';
    	this.settings.each(function(select, ch){
                if (parseInt(select.value) || (!select.value && (!select.options[1] || !select.options[1].value))){
	            keySt += select.value + ',';
	        }
		else {
		     keySt += select.options[1].value + ',';
		}
    	});
	    keySt = keySt.substr(0, keySt.length - 1);
        stStatus.onConfigure(keySt, this.settings, key);
    }

	//Amasty code for Automatically select attributes that have one single value
    if (('undefined' != typeof(amConfAutoSelectAttribute) && amConfAutoSelectAttribute)
        || ('undefined' != typeof(amStAutoSelectAttribute) && amStAutoSelectAttribute)
    ) {
        var nextSet = element.nextSetting;
        if(nextSet && nextSet.options.length == 2
            && !nextSet.options[1].selected
            && element && !element.options[0].selected
        ){
            nextSet.options[1].selected = true;
            this.configureElement(nextSet);
        }
    }
    /* compatibility with PreOrder*/
    if ('undefined' != typeof(preorderState)) {
        preorderState.update();
    }

    /**add option label start*/
	var label = "";
	element.config.options.each(function(option){
		if (option.id == element.value) {
		    label = option.label;
        }
	});
	if (label) {
	    label = " - " + label;
        var parent = element.parentNode.parentNode.previousElementSibling;
        if ( typeof(parent) != 'undefined'
            && parent != null && parent.nodeName == "DT"
            && (conteiner = parent.select("label")[0])
        ) {
            if (tmp = conteiner.select('span.amconf-label')[0]) {
                tmp.innerHTML = label;
            } else {
                var tmp = document.createElement('span');
                tmp.addClassName('amconf-label');
                conteiner.appendChild(tmp);
                tmp.innerHTML = label;
            }
        }
    }
    /**add option label end*/

    /*add params to hash start*/
    if (addParamsToHash) {
        var hash = window.location.hash;
        var attributeId = element.id.replace(/[a-z-]*/, '');

        if (hash.indexOf(attributeId + '=') >= 0) {
            var replaceText = new RegExp(attributeId + '=' + '.*');
            if(optionId) {
                hash = hash.replace(replaceText, attributeId + '=' + optionId);
            }
            else{
                hash = hash.replace(replaceText, "");
            }
        } else {
            if (hash.indexOf('#') >= 0) {
                hash = hash + '&' + attributeId + '=' + optionId;
            }
            else {
                hash = hash + '#' + attributeId + '=' + optionId;
            }
        }

        window.location.replace(window.location.href.split('#')[0] + hash);
    }
    /*add params to hash end*/
};

Product.Config.prototype.amPreselectOneOptionAttribute  = function () {
    if(('undefined' != typeof(amConfAutoSelectAttribute) && amConfAutoSelectAttribute) ||('undefined' != typeof(amStAutoSelectAttribute) && amStAutoSelectAttribute)){
        var select  = this.settings[0];
        if(select && select.options.length == 2 && !select.options[1].selected){
            select.options[1].selected = true;
            this.configureElement(select);
        }
    }
};

Product.Config.prototype.configureHr = function(event){
    var element = Event.element(event);
    element.nextSibling.click();
};

Product.Config.prototype.configureImage = function(event){
    var element     = Event.element(event),
        attributeId = element.parentNode.id.replace(/[a-z-]*/, ''),
        optionId    = element.id.replace(/[a-z-]*/, '');

    this.selectImage(element);

    var dropDown = $('attribute' + attributeId);
    dropDown.value = optionId;
    this.configureElement(dropDown);
    /* fix for sm ajax cart*/
    if($$('body.sm_market').length > 0){
        jQuery('#attribute' + attributeId).trigger("change");
    }
};

Product.Config.prototype.selectImage = function(element)
{
    attributeId = element.parentNode.id.replace(/[a-z-]*/, '');
    $('amconf-images-' + attributeId).childElements().each(function(child){
        child.childElements().each(function(children){
            children.removeClassName('amconf-image-selected');
        });
    });
    element.addClassName('amconf-image-selected');
};

Product.Config.prototype.processEmpty = function()
{
    $$('.super-attribute-select').each(function(select) {
        var attributeId = select.id.replace(/[a-z]*/, '');
        if (select.disabled)
        {
            var amconfImages = $('amconf-images-' + attributeId);
            if (amconfImages)
            {
                amconfImages.parentNode.removeChild(amconfImages);
            }
            var holder = select.parentNode,
                holderDiv = document.createElement('div');
            holderDiv.addClassName('amconf-images-container');
            holderDiv.id = 'amconf-images-' + attributeId;
            if ('undefined' != typeof(confData))
            {
            	holderDiv.innerHTML = confData.optionProducts.textNotAvailable;
            } else {
            	holderDiv.innerHTML = "";
            }
            holder.insertBefore(holderDiv, select);

        } else if (!select.disabled && !$(select).hasClassName("no-display")) {
            var element = $(select.parentNode).select('#amconf-images-' + attributeId)[0];
            if (typeof confData != 'undefined'
                && typeof element != 'undefined'
                && element.innerHTML == confData.optionProducts.textNotAvailable
            ) {
                element.parentNode.removeChild(element);
            }
        }
    }.bind(this));
};

Product.Config.prototype.clearConfig = function()
{
    this.settings[0].value = "";
    this.configureElement(this.settings[0]);
    $$('span.amconf-label').each(function (el){
	    el.remove();
    });
    return false;
};

//start code for reload simple price
Product.Config.prototype.reloadSimplePrice = function(key)
{
    var currentPrice = 0;
    if (confData.hasKey(key) && confData.getData(key, 'price_html'))
    {
        this.reloadPriceHtml(confData.getData(key, 'price_html'), confData.getData(key, 'price_clone_html'));
        currentPrice = confData.getData(key, 'price_without_tax');
    } else
        if (true == confData.getDefault('set') && confData.getDefault('price_html'))// setting values of default product
    {
        this.reloadPriceHtml(confData.getDefault('price_html'), confData.getDefault('price_clone_html'), 1);
        currentPrice = confData.getDefault('price_without_tax');
    }

    if (currentPrice)
    {
        if (!confData.getDefault('price_without_tax'))
        {
            confData.saveDefault('price_without_tax', optionsPrice.productPrice);
        }
        optionsPrice.productPrice = currentPrice;
        optionsPrice.reload();

        return currentPrice;
    }
};

Product.Config.prototype.reloadPriceHtml = function(priceHtml, priceCloneHtml, defaultFlag)
{
    // convert div.price-box into price info container
    // top price box
    var priceSelector = '.product-shop .price-box';
    if (!$$(priceSelector).length) {
        priceSelector += ', #product_addtocart_form .price-box';//fix for custom themes
    }
    $$(priceSelector).each(function(container)
    {
        if (!confData.getDefault('price_html'))
        {
            confData.saveDefault('price_html', container.innerHTML);
        }
        container.addClassName('amconf_price_container');
    }.bind(this));

    $$('.product-shop .tax-details, .product-shop .tier-prices').each(function(container)
    {
        container.remove();
    }.bind(this));

    $$('.amconf_price_container').each(function(container)
    {
        if (defaultFlag) {
            container.innerHTML = priceHtml;
            container.removeClassName('amconf_price_container');
        } else {
            container.outerHTML = priceHtml;
        }
    }.bind(this));

    // bottom price box
    if (priceCloneHtml)
    {
        $$('.product-options-bottom .price-box').each(function(container)
        {
            if (!confData.getDefault('price_clone_html'))
            {
                confData.saveDefault('price_clone_html', container.innerHTML);
            }
            container.addClassName('amconf_price_clone_container');
        }.bind(this));

        $$('.product-options-bottom .tax-details, .product-options-bottom .tier-prices').each(function(container)
        {
            container.remove();
        }.bind(this));

        $$('.amconf_price_clone_container').each(function(container)
        {
            if (defaultFlag) {
                container.innerHTML = priceCloneHtml;
                container.removeClassName('amconf_price_clone_container');
            } else {
                container.outerHTML = priceCloneHtml;
            }
        }.bind(this));
    }
};

Product.Config.prototype.amOrig_getOptionLabel = Product.Config.prototype.getOptionLabel;
Product.Config.prototype.getOptionLabel = function(option, price){
    if ('undefined' == typeof(confData)
        || confData.optionProducts.useSimplePrice == "0"
    ) {
        return this.amOrig_getOptionLabel(option, price);
    } else {
        var str = option.label;
        if ('undefined' == typeof(confData.optionProducts.dropdownPrice)
            || confData.optionProducts.dropdownPrice == '0'
        ) {
            return str;
        }

        /*find selected key*/
        var selectedKey = "";
        for (var i = 0; i < this.settings.length; i++){
            if(parseInt(this.settings[i].value) > 0){
                selectedKey += this.settings[i].value + ',';
            }
        }
        var trimSelectedKey = selectedKey.substr(0, selectedKey.length - 1);
        var countKeys = selectedKey.split(",").length - 1;
        if(trimSelectedKey){
            if(countKeys < this.settings.length){
                var keyCheck  = selectedKey + option.id;
            }
            else{
                var keyCheckParts = explode(',', trimSelectedKey);
                keyCheckParts[keyCheckParts.length - 1] = option.id;
                var keyCheck = implode(',', keyCheckParts);
            }
        } else{
            keyCheck = option.id;
        }

        if( confData['optionProducts'][keyCheck] && confData['optionProducts'][keyCheck]['price'] ) {
            var simplePrice = confData['optionProducts'][keyCheck]['price'];
            var parentPrice = confData['optionProducts']['parentPrice'];
            var type = confData['optionProducts']['dropdownPrice'];
            if ( type == "2" ) {// show actual price
                if ( parentPrice != simplePrice ) {
                    str += ' ' + this.formatPrice(simplePrice, true);
                    pos = str.indexOf("+");
                    str = str.substr(0, pos) + str.substr(pos + 1, str.length);
                }
            }
            else{//show price difference
                var selectedPrice;
                try{
                    if(option['attr']) {
                        var element = $("attribute" + option['attr'].id);
                    }
                    else{
                        var element = $$("option[value=" + option.id + "]").first().up();
                    }
                }catch(ex){}

                if (element && element.value) {
                    var keyCheckPartsSelected = explode(',', trimSelectedKey);
                    keyCheckPartsSelected[keyCheckPartsSelected.length - 1] = element.value;
                    var keyCheckSelected = implode(',', keyCheckPartsSelected);
                    selectedPrice = confData['optionProducts'][keyCheckSelected]['price'];
                }
                else {
                    selectedPrice = parentPrice;
                }

                var price = simplePrice - selectedPrice;
                price = parseFloat(price);
                if ( this.taxConfig.includeTax ) {
                    var tax = price / (100 + this.taxConfig.defaultTax) * this.taxConfig.defaultTax;
                    var excl = price - tax;
                    var incl = excl*(1+(this.taxConfig.currentTax/100));
                } else {
                    var tax = price * (this.taxConfig.currentTax / 100);
                    var excl = price;
                    var incl = excl + tax;
                }

                if (this.taxConfig.showIncludeTax || this.taxConfig.showBothPrices) {
                    price = incl;
                } else {
                    price = excl;
                }

                if (price) {
                    str += ' ' + this.formatPrice(price, true);
                }
            }
        }

        return str;
    }
};

Event.observe(window, 'load', function(){
    if ('undefined' != typeof(confData) && confData.optionProducts.useSimplePrice == "1")
    {
        confData.reloadOptions();
    }
    if ('undefined' != typeof(spConfig) )
    {
        spConfig.amPreselectOneOptionAttribute();
    }
});

Product.Config.prototype.updateData = function(key)
{
    if ('undefined' == typeof(confData) || key == this.currentUpdateDataKey ) {
        return false;
    } else {
        this.currentUpdateDataKey = key;
    }

    if (confData.hasKey(key)) {
        // getting values of selected configuration
        for(var index in confData.optionProducts.selector) {
            this.updateSimpleData(index, confData.optionProducts.selector[index], key);
        }

        decorateTable('product-attribute-specs-table');
    } else {
        // setting values of default product
        for (var index in confData.optionProducts.selector) {
            this.getDefaultSimpleData(index, confData.optionProducts.selector[index]);
        }
    }

    var reloadUrl = confData.getData(key, 'media_url');
    if (!reloadUrl) {
        var keyArray = key.split(',');
        if (keyArray.length > 1) { //get url for first option
            reloadUrl = confData.getData(keyArray[0], 'media_url');
        }
    }
    this.reloadImageBlock(reloadUrl);
};

Product.Config.prototype.reloadImageBlock = function(url)
{
    if (!url || url == this.reloadImageBlockUrl) {
        return false;
    } else {
        this.reloadImageBlockUrl = url;
    }

    // should reload images
    var tmpContainer = $$(confData.optionProducts.imageContainer).first();
    if(!tmpContainer) {
        console.debug("Please set correctly CSS selector at module configuration!");
    } else {
        if($$(confData.productImageSelector).length) {
            this.mainImageHeight = $$(confData.productImageSelector).first().getHeight();
        }

        if($$(confData.productGallerySelector).length) {
            this.thimbWidth =  $$(confData.productGallerySelector).first().getWidth();
        }

        var me = this;
        if (typeof confData.cachedImages[this.reloadImageBlockUrl] !== 'undefined') {
            $(tmpContainer).update(confData.cachedImages[this.reloadImageBlockUrl]);
            this.updateImageData(me);
        } else {
            new Ajax.Updater(tmpContainer, url, {
                evalScripts: true,
                onComplete: function (response) {
                    confData.cachedImages[response.request.url] = response.responseText;
                    me.updateImageData(me);
                }
            });
        }
    }
};

/* prevent jumping image, initialize features for image */ 
Product.Config.prototype.updateImageData = function (config) {
    if(config && config.mainImageHeight && $$(confData.productImageSelector).length) {
        $$(confData.productImageSelector).first()
            .setStyle({minHeight: config.mainImageHeight + 'px'})
            .onload = function() {
            this.setStyle({minHeight: '0'});
        };
    }

    if('undefined' != typeof(AmZoomerObj)) {
        if($$('.zoomContainer')[0]) {
            $$('.zoomContainer')[0].remove();
        }

        if (!config.thimbWidth) {
            config.thimbWidth = $$(confData.productGallerySelector).getWidth();
        }

        if (config.thimbWidth > 0) {
            /* fix for issue with not loaded image width = 0 */
            $$(confData.productGallerySelector).each(function(item) {
                item.setStyle({'minWidth' : config.thimbWidth + 'px'});
            });
        }

        AmZoomerObj.loadZoom();
    }

    if('undefined' != typeof(MagicScroll)) {
        MagicScroll.refresh();
    }

    if ('undefined' != typeof(MagicZoom)) {
        MagicZoom.refresh();
    }

    if('undefined' != typeof(ProductMediaManager)) {
        ProductMediaManager.init();
    }

    if (typeof jQuery.CloudZoom === 'function') {
        jQuery('.cloud-zoom, .cloud-zoom-gallery').CloudZoom();
    }
};


Product.Config.prototype.updateSimpleData= function(type, selector, key){
    if (confData.getData(key, type))
    {
        $$(selector).each(function(container){
            if(container.hasClassName('data-table')) {
                container = container.parentNode;
            }

            if (!confData.getDefault(type)) {
                confData.saveDefault(type, container.innerHTML);
            }

            if(confData.getData(key, type) != "") {
                container.innerHTML = confData.getData(key, type);
            }
        }.bind(this));
    }
};

Product.Config.prototype.getDefaultSimpleData= function(type, selector) {
    if (confData.getDefault(type))
    {
       $$(selector).each(function(container){
            if(container.hasClassName('data-table')) container = container.parentNode;
            container.innerHTML = confData.getDefault(type);
        }.bind(this));
    }
};

function explode (delimiter, string, limit)
{
    var emptyArray = { 0: '' };

    // third argument is not required
    if ( arguments.length < 2 ||
        typeof arguments[0] == 'undefined' ||
        typeof arguments[1] == 'undefined' )
    {
        return null;
    }

    if ( delimiter === '' ||
        delimiter === false ||
        delimiter === null )
    {
        return false;
    }

    if ( typeof delimiter == 'function' ||
        typeof delimiter == 'object' ||
        typeof string == 'function' ||
        typeof string == 'object' )
    {
        return emptyArray;
    }

    if ( delimiter === true ) {
        delimiter = '1';
    }

    if (!limit) {
        return string.toString().split(delimiter.toString());
    } else {
        // support for limit argument
        var splitted = string.toString().split(delimiter.toString());
        var partA = splitted.splice(0, limit - 1);
        var partB = splitted.join(delimiter.toString());
        partA.push(partB);
        return partA;
    }
}

function implode (glue, pieces) {
    var i = '', retVal='', tGlue='';
    if (arguments.length === 1) {
        pieces = glue;
        glue = '';
    }
    if (typeof(pieces) === 'object') {
        if (pieces instanceof Array) {
            return pieces.join(glue);
        }
        else {
            for (i in pieces) {
                retVal += tGlue + pieces[i];
                tGlue = glue;
            }
            return retVal;
        }
    }
    else {
        return pieces;
    }
}

// extension Code End
