
class VisideaMagento {

    constructor(visidea, shop, public_token)
    {

        // Initialize fields
        this.shop = shop;
        this.public_token = public_token;
        this.visidea = {};
        this.hotpoints = [];
        this.resizeFactor = 1;
        // this.whenAvailable('Visidea', function() {

        // Initialize visidea
        this.visidea = visidea;
        // console.log(this.visidea)

        // Load necessary scripts and styles
        this.loadCss(
            `
            .visidea {
                margin: 2em 0;
            }
            .visidea h2 {
                text-align: center;
            }
            .visidea a {
                outline: 0;
                color:black;
            }
            .visidea p {
                margin:0;
            }
            .visidea .visidea__product {
                padding: 0px 0.5rem;
                text-align: center;
                outline: 0;
                line-height: 1.5;
            }
            .visidea .visidea__product-heading, .visidea .visidea__product-brand {
                margin: 0;
            }
            .visidea .product-item-details {
                text-align: center;
            }
            .visidea img.visidea__product-image {
                max-width: 100%;
                max-height: 100%;
            }
            .visidea .slick-next:before, .visidea .slick-prev:before {
                color: #000!important;
                opacity: .25;
                padding-left:0;
                padding-right:0;
            }
            .slick-slide img {
                max-height: 100%;
            }
            .slick-next, .slick-prev {
                z-index: 9999;
                display: inline-block!important;
                overflow: hidden;
            }
            .slick-prev {
                left: 20px;
                float: left;
                margin: 0;
                text-indent: 0;
            }
            .slick-next {
                right: 20px;
                float: left;
                margin: 0;
                text-indent: 0;
            }
            .woocommerce.widget_product_search {
                white-space: nowrap;
            }
            .visidea-visualsearch-icon {
                display: inline-block;
                float:right;
                width:28px;
                margin-left: 10px;
            }
            .visidea-visualsearch-icon img {
                // margin-top: 3px;
                width:28px;
                height:28px;
            }
            .woocommerce-product-search {
                display: inline-block;
            }
            .visidea-visualsearch {
                display:none;
                background:white;
                position:fixed;
                z-index: 99999;
                top:0;
                bottom:0;
                right:0;
                left:0;
            }
            .visidea-visualsearch p {
                margin:0;
            }
            .visidea-visualsearch a {
                color:black;
            }
            .visidea-visualsearch__exit {
                z-index: 99999;
                position: absolute;
                right: 0;
                float:right;
                background-image:url("https://cdn.visidea.ai/imgs/icons/svg/visidea_cancelcircle.svg");
                width:30px;
                height:30px;
                margin:13px;
            }
            .visidea-visualsearch__exit:hover {
                cursor: pointer;
            }
            @media only screen and (min-width: 768px) {
                .visidea-visualsearch__exit {
                    margin: 10px;
                }
            }
            @media only screen and (min-width: 1024px) {
            }
            `
        );

        this.addCustomCss();
        this.initializeVisualSearch();

        // }.bind(this));

    }

    setUser(user_id)
    {
        var old_user_id = localStorage.getItem("Visidea.user");
        if (old_user_id == null) {
            if (user_id == 0) {
                user_id = this.visidea.uuidv4();
            }    
            localStorage.setItem("Visidea.user", user_id);
        } else {
            if (user_id == 0) {
                user_id = old_user_id
            }
        }
        if (old_user_id != user_id && old_user_id != null) {
            localStorage.setItem("Visidea.user", user_id);
            this.visidea.merge_users(old_user_id, user_id);
        }
        return user_id;
    }

    async addCustomCss()
    {
        // Wait for 'visidea.conf' to exist
        while (!this.visidea.conf) {
            //console.log('wait');
            await new Promise(r => setTimeout(r,10))
        }
        if (this.visidea.conf.css && this.visidea.conf.css != '')
            this.loadCss(this.visidea.conf.css)
    }

    async initializeVisualSearch()
    {
        console.log('initializeVisualSearch')
        console.log(this.visidea)
        // Wait for 'visidea.conf' to exist
        while (!this.visidea.conf) {
            //console.log('wait');
            await new Promise(r => setTimeout(r,10))
        }

        if (!this.visidea.conf.has_visualsearch)
            return;

        var icon = '<svg id="Livello_1" data-name="Livello 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40"><path d="M32.41082,7.60858a17.528,17.528,0,1,0-2.61108,26.92279,3.41827,3.41827,0,1,0-1.25586-1.56732A15.52619,15.52619,0,1,1,35.34149,22.4455a1.0004,1.0004,0,1,0,1.97656.31054A17.60924,17.60924,0,0,0,32.41082,7.60858Zm-1.627,23.03027a1.40822,1.40822,0,0,1,.918-.33984c.03613,0,.07324.002.11035.00488a1.39761,1.39761,0,0,1,.96484.48926,1.41453,1.41453,0,0,1-.15332,1.99317v.001a1.41428,1.41428,0,0,1-1.83984-2.14844Z"/><path d="M19.82728,16.32285a3.90561,3.90561,0,1,0,3.90564,3.90564A3.90558,3.90558,0,0,0,19.82728,16.32285Zm0,5.3974a1.49179,1.49179,0,1,1,1.49182-1.49176A1.49183,1.49183,0,0,1,19.82728,21.72025Z"/><path d="M27.16382,13.33341H24.04664l-1.81348-1.49414a2.00026,2.00026,0,0,0-1.27344-.458H18.6941a1.99318,1.99318,0,0,0-1.27149.457l-1.8164,1.49511H12.491a2.0026,2.0026,0,0,0-2,2v9.79a2.0026,2.0026,0,0,0,2,2H27.16382a2.0026,2.0026,0,0,0,2-2v-9.79A2.0026,2.0026,0,0,0,27.16382,13.33341Zm-3.10546.001h0Zm3.10546,11.78906H12.491v-9.79h3.11524a2.00523,2.00523,0,0,0,1.27246-.45605l1.81543-1.49609h2.2666l1.81543,1.49609a2.00218,2.00218,0,0,0,1.27148.45605h3.11621Z"/></svg>';
        var html = '<div class="visidea-visualsearch-icon"><a href="javascript:void(0)" onclick="visideaMagento.showVisualSearch()">'+icon+'</a></div>';

        if (this.visidea.conf.visualsearch_show_after && this.visidea.conf.visualsearch_show_after != '')
            jQuery(this.visidea.conf.visualsearch_show_after).after(html);
        else
            jQuery('.block-search').before(html);

        var html = '<div class="visidea-visualsearch" id="visidea-visualsearch">';
        html = html + '<a class="visidea-visualsearch__exit" onclick="visideaMagento.hideVisualSearch()"></a>';
        html = html + '<div id="visidea-vs-root" website="'+this.shop+'" public_token="'+this.public_token+'"></div>';      
        html = html + '</div>';

        jQuery('body').append(html);

        this.loadScript('https://cdn.visidea.ai/visual-search/js/main.js?ver=1.4.0');

    }    

    showVisualSearch()
    {
        jQuery('.visidea-visualsearch').css('display','block');
        jQuery('html').scrollTop(0);
        // this.fixContentHeight();
        jQuery('body').css('overflow-y','hidden');
    }

    hideVisualSearch()
    {
        jQuery('.visidea-visualsearch').css('display','none');
        jQuery('body').css('overflow-y','auto');

        const url = new URL(window.location);
        url.searchParams.delete('visidea');
        url.searchParams.delete('visideaitem');
        window.history.pushState({}, '', url);
    }

    loadStyle(src)
    {
        var link = document.createElement('link');
        link.href = src;
        link.type = "text/css";
        link.rel = "stylesheet";
        link.media = "screen,print";
        document.head.appendChild(link);
    }

    loadCss(styles)
    {
        var styleSheet = document.createElement("style");
        styleSheet.type = "text/css";
        styleSheet.innerHTML = styles;
        document.head.appendChild(styleSheet);
    }

    loadScript(src)
    {
        var script = document.createElement('script');
        script.onload = function () {
            //do stuff with the script
        };
        script.src = src;
        document.head.appendChild(script);
    }

    whenAvailable(name, callback)
    {
        var interval = 10; // ms
        var self = this;
        window.setTimeout(
            function () {
                if (window[name]) {
                    callback(window[name]);
                } else {
                    window.setTimeout(self.whenAvailable(name, callback), interval);
                }
            }, interval
        );
    }

    whenExists(name, callback)
    {
        var interval = 10; // ms
        window.setTimeout(
            function () {
                if (eval(name) != undefined) {
                    callback();
                } else {
                    window.setTimeout(arguments.callee, interval);
                }
            }, interval
        );
    }

}
