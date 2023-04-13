# OGGEH HTML Parser

This is a free client library introduces our custom HTML tag `<oggeh />` for automating our Content Delivery API. Perfect for skills limited to HTML and CSS only.

> You need a Developer Account to obtain your App API Keys, if you don't have an account, [request](https://account.oggeh.com/request) one now.

## Getting Started

1. First, you need to enter your App API Key in `index.php`
```php
OGGEH::configure('domain', 'domain.ltd');
OGGEH::configure('api_key', '[APP_API_KEY]');
```
2. For local environment, you need to enter your App Sandbox Key as well in `index.php`, and set _sandbox_ setting to `true`
```php
OGGEH::configure('sandbox_key', '[APP_SANDBOX_KEY]');
OGGEH::configure('sandbox', true);
```
3. Optionally, you can configure your own Frontend dictionary for translating page custom model attributes as follows:
```php
OGGEH::configure('i18n', array(
  'category'=>array(
    'en'=>'Category',
    'ar'=>'التصنيف'
  ),
  'client'=>array(
    'en'=>'Client',
    'ar'=>'العميل'
  )
));
```
4. Edit your _hosts_ file and append:
```
127.0.0.1 app.domain.ltd
```
5. Preview example template in browser at http://app.domain.ltd

## IMPORTANT

You will not be charged for your apps in development mode. Please do *not* use _Sandbox_ headers in production mode to avoid blocking your App along with your Developer Account for violating our terms and conditions!
If you're planning to use this example, remove the `SandBox` header from JavaScript (_assets/js/main.js @line 109_)

## How it Works

The library accepts the following URL Segments:
```
http://domain.ltd/?lang=&module=&param1=&param2=
```

If you're familiar with apache rewrite rules, you can rename `htaccess.txt` to `.htaccess` which redirects all requests at your Frontend Template to the above index file as follows:
```
http://domain.ltd/:lang/:module/:param1/:param2
```
Remember to enable `rewrite` settings at `index.php` before activating the above file:
```
OGGEH::configure('rewrite', true);
```

URL Segment | Description
--- | ---
domain.ltd | Your App domain as entered during creation.
:lang | URL language code (_for example: en_), this is how you pass target language to our API Requests.
:module | Represents which content model you want to retreive from your customer's content (_page, album, .. etc_).
:param1 | Represents additional filtering parameter to the selected model (_for example: page-unique-identifier_).
:param2 | Represents an extra parameter to the selected model.

The library maps each model from your Frontend Request URL above to an HTML template file inside the _tpl_ directory.

As of the home page, you need to keep a default HTML file _tpl/home.html_.
* For rendering single news post _tpl/news.single.html_.
* For rendering single photo album _tpl/album.photo.html_.
* For rendering single audio album _tpl/album.audio.html_.
* For rendering single video album _tpl/album.video.html_.
* For rendering single file album _tpl/album.file.html_.
* You can use _tpl/header.html_ and _tpl/footer.html_ as well, and the library will automatically wrap those around the rest of your HTML template files.
* You can use _tpl/404.html_ for invalid requests.
* You can use _tpl/inactive.html_ to be displayed when your App is not in production mode.

### Usage

Our custom HTML tag `<oggeh />` can be used as follows:

#### 1. General variables available are:
* `$lang`: URL first parameter (_check URL segments above_).
* `$module`: URL second parameter (_check URL segments above_).
* `$param1`: URL third parameter (_check URL segments above_).
* `$param2`: URL fourth parameter (_check URL segments above_).
* `$oggeh-phrase`: translates specific key from your own Frontend dictionary (_{$oggeh-phrase|phrase-custom-key}_).
* `$flag`: maps the language code to a country code (_defined at locale.json_).
* `$oggeh-clone-repeat`: has a copy of the preceding element which has `oggeh-repeat` flag (_check building App navigation below_).

#### 2. Inline arrtibutes forms the request body (_the parser converts those into JSON later_), for example, the following attributes:
```html
<oggeh method="get.app" select="title,meta" />
```
Simulates the following request:
```
curl -H "Content-Type: application/json" -X POST -d '[{"method":"get.app","select":"title,meta"}]' https://api.oggeh.com/?api_key=[APP_API_KEY]&lang=en
```

#### 3. For printing a single value, you can just use a self-closing tag, where the API response will be a string:
```html
<oggeh method="get.app" select="title" />
```
Or you can grab your `select` parameters as variables:
```html
<oggeh method="get.app" select="title">
	<h2>{$title}</h2>
</oggeh>
```

#### 4. For iterating over API response, use `oggeh-repeat` inline flag as follows:
```html
<oggeh method="get.page" key="{$param1}" select="blocks" block-type="rte">
  <p oggeh-repeat>
    {$html}
  </p>
</oggeh>
```
	
Where the variable `$html` is a direct property of each iteration of your target `blocks` at the API response.

	NOTE: always use unique block snippet parent tag (do not reuse in child tags).

There are more to the above iterating approach:

##### a. If the response is an object (_or an associative array_), use `$var.key` and `$var.value` instead, where `$var` is your target selection, for example:
```html
<oggeh method="get.app" select="social">
  <ul class="icons">
    <li oggeh-repeat><a href="{$social.value}" class="icon fa-{$social.key}"><span class="label">{$social.key}</span></a></li>
  </ul>
</oggeh>
```

##### b. If the response is a simple array (_elements are not objects_), use `$` instead, for example:
```html
<oggeh method="get.app" select="languages">
  <p class="lang" oggeh-repeat>
    {$}
  </p>
</oggeh>
```
In the above example, you might want to create a language switcher, which maintains the current URL, just changes the language code. In order to do so, use `$oggeh-switch` variable as follows:
```html
<oggeh method="get.app" select="languages">
  <p class="lang" oggeh-repeat>
    <a href="{$oggeh-switch|$}">
      <span class="flag-icon flag-icon-{$flag}"></span>
    </a>
  </p>
</oggeh>
```
Where `$flag` is an optional variable, which you can use to map the language code to a country code (_defined at locale.json_).

##### c. If you want to iterate over a specific property at the API response, use the `iterate` attribute to specify that property, for example:
```html
<oggeh method="get.album" label="{$param2}" select="caption,thumbnail,regular" iterate="items">
  <div oggeh-repeat>
    <a href="{$regular.url}">
      <img src="{$thumbnail.url}" alt="{$caption.title}" />
    </a>
    <span>{$caption.title}</span>
    <p>{$caption.description}</p>
  </div>
</oggeh>
```

#### 5. For building your navigation links, you might want to:

##### a. Mark the current page as `active`, for that use the property `oggeh-match` anywhere inside your inner markup as follows:
```html
<oggeh method="get.pages" select="key,subject">
  <li>
    <a href="/{$lang}/" oggeh-match="home|active">{$oggeh-phrase|home}</a>
  </li>
</oggeh>
```
The above example will add a class name `active` to the anchor tag only if the URL `:module` matches `home`.

	NOTE: using variables in property `oggeh-match` works only in inner html, not on the repeatable item itself.

##### b. Nest items dynamically (_to match the pages tree at the CMS_), for that use `oggeh-nest` inline flag, along with the special variable `$oggeh-clone-repeat`. It has a copy of the preceding element which has `oggeh-repeat` flag, for example:
```html
<oggeh method="get.pages" select="key,subject">
	<li oggeh-repeat>
    <a href="/{$lang}/page/{$key}" oggeh-match="page/{$key}|active">{$subject}</a>
  </li>
  <li oggeh-nest>
    <span class="opener">{$subject}</span>
    <ul>
      {$oggeh-clone-repeat}
    </ul>
  </li>
</oggeh>
```

#### 6. For building your page blocks matching the same grid layout at the CMS, use `oggeh-snippet` inline flag to describe your custom markup for each block type. There are 2 additional properties you can use to mark each snippet:
* Block `type` property: accepts `rte`, `media`, `files`, or `table`.
* Media block `filter` property: accepts `photo`, `audio`, or `video`.
	
	NOTE: always use unique block snippet parent tag (do not reuse in child tags).

The parser automatically iterates over the proper target at the API response, for example:
```html
<oggeh method="get.page" key="{$param1}" select="blocks">
  <article class="{$size_x}u 12u$(small)" type="rte" oggeh-snippet>
    <p oggeh-repeat>
      {$html}
    </p>
  </article>
  <article class="{$size_x}u 12u$(small)" type="media" filter="photo" oggeh-snippet>
		<ul>
      <li oggeh-repeat>
        <img src="{$regular.url}" alt="{$caption.title}" />
      </li>
    </ul>
  </article>
  <article class="{$size_x}u 12u$(small)" type="media" filter="audio" oggeh-snippet>
    <ul>
      <li oggeh-repeat>
        <a href="{$url}">{$caption.title}</a>
        <p>{$caption.description}</p>
      </li>
    </ul>
  </article>
  <article class="{$size_x}u 12u$(small)" type="media" filter="video" oggeh-snippet>
    <ul>
      <li oggeh-repeat>
        <a href="{$url}">{$caption.title}</a>
        <p>{$caption.description}</p>
      </li>
    </ul>
  </article>
  <article class="{$size_x}u 12u$(small)" type="files" oggeh-snippet>
    <ul>
      <li oggeh-repeat>
        <a href="{$url}">{$caption.title}</a>
        <p>{$caption.description}</p>
      </li>
    </ul>
  </article>
  <article type="table" oggeh-snippet>
    {$table}
  </article>
</oggeh>
```
Where the variable `$size_x` represents the column size for each snippet (_12 columns grid_).

#### 7. For building you page form, use `oggeh-field` inline flag to describe your custom markup for each form field type, and use `oggeh-static` inline flag to describe your custom markup for any additional markup you want to add (_like `submit` and `reset` buttons_). There are 3 additional variables you can use to print each field:
* Field `$name` variable: represents field name (_plain text_).
* Field `$label` variable: represents field label (_plain text_).
* Field `$control` variable: represents field HTML markup (_HTML text_).
	
	NOTE: always use unique field parent tag (do not reuse in child tags).

There is 2 additional property you can use to mark each field:
* Field `subtype` property: accepts `text`, `email`, `password`, `number` or `color`.
* Field `inject` property: add custom classes to the target tag, and might accept a single condition to be applied `required`, `toggle`, or any other boolean attribute at the API response.

	NOTE: conditional inline tag class inject works only in inner html (not applying to control).

The parser automatically iterates over the proper target at the API response, for example:
```html
<oggeh method="get.page" key="{$param1}" select="blocks" block-type="form" iterate="form">
  <form method="post" action="{$endpoint}/?api_key={$api_key}&lang={$lang}">
    <div type="text" subtype="text" inject="validate-required|required" oggeh-field>
      <label for="{$name}">{$label}</label>
      {$control}
    </div>
    <div type="select" inject="validate-required|required" oggeh-field>
      <label for="{$name}">{$label}</label>
      {$control}
    </div>
    <div type="radio-group" inject="validate-required|required" oggeh-field>
      {$control}
      <label for="{$name}">{$label}</label>
    </div>
    <div type="checkbox" inject="validate-required|required" oggeh-field>
      {$control}
      <label for="{$name}" inject="input-checkbox--switch|toggle">{$label}</label>
    </div>
    <div type="checkbox-group" inject="validate-required|required" oggeh-field>
      {$control}
      <label for="{$name}">{$label}</label>
    </div>
    <div type="date" inject="datepicker" oggeh-field>
      <label for="{$name}">{$label}</label>
      {$control}
    </div>
    <div class="12u" type="file" oggeh-field>
      {$control}
      <label for="{$name}">{$label}</label>
    </div>
    <div inject="validate-required|required" oggeh-field>
      <label for="{$name}">{$label}</label>
      {$control}
    </div>
    <div oggeh-static>
      <input type="submit" value="{$oggeh-phrase|submit}" />
    </div>
  </form>
</oggeh>
```
We recommend that to include an `oggeh-field` without specifying its type, that markup will be used as the default wrapper for any missing type.

#### 8. For building your albums, use `oggeh-album` inline flag to describe your custom markup for each album media type. There are 1 additional property you can use to mark each snippet:
* Album type `media` property: accepts `photo`, `audio`,`video`, or `file`.
  
  NOTE: always use unique block snippet parent tag (do not reuse in child tags).

The parser automatically iterates over the proper target at the API response, for example:
```html
<oggeh method="get.albums" select="media,label,cover">
  <div media="photo" oggeh-album>
    <a href="/{$lang}/album/photo/{$label}">
      <img src="{$cover.regular.url}" alt="{$label}" />
      <span>{$label}</span>
    </a>
  </div>
  <div media="audio" oggeh-album>
    <a href="/{$lang}/album/audio/{$label}">
      <img src="{$cover.regular.url}" alt="{$label}" />
      <span>{$label}</span>
    </a>
  </div>
  <div media="video" oggeh-album>
    <a href="/{$lang}/album/video/{$label}">
      <img src="{$cover.regular.url}" alt="{$label}" />
      <span>{$label}</span>
    </a>
  </div>
  <div media="file" oggeh-album>
    <a href="/{$lang}/album/file/{$label}">
      <img src="{$cover.regular.url}" alt="{$label}" />
      <span>{$label}</span>
    </a>
  </div>
</oggeh>
```

### Examples

#### App Navigation Example
```html
<oggeh method="get.pages" select="key,subject">
  <li>
    <a href="/{$lang}/" oggeh-match="home|active">{$oggeh-phrase|home}</a>
  </li>
  <li oggeh-repeat>
    <!-- NOTE: using variables in property `oggeh-match` works only in inner html -->
    <a href="/{$lang}/page/{$key}" oggeh-match="page/{$key}|active">{$subject}</a>
  </li>
  <li oggeh-nest>
    <span class="opener">{$subject}</span>
    <ul>
      <!-- NOTE: variable `$oggeh-clone-repeat` has a copy of the preceding element which has `oggeh-repeat` flag -->
      {$oggeh-clone-repeat}
    </ul>
  </li>
  <li>
    <a href="/{$lang}/album" oggeh-match="album|active">{$oggeh-phrase|album}</a>
  </li>
  <li oggeh-match="news|active">
    <a href="/{$lang}/news" oggeh-match="news|active">{$oggeh-phrase|all-news}</a>
  </li>
  <li oggeh-match="contact|active">
    <a href="/{$lang}/contact" oggeh-match="contact|active">{$oggeh-phrase|contact-us}</a>
  </li>
</oggeh>
```

#### Only Page Subject Example
```html
<oggeh method="get.page" key="{$param1}" select="subject" />
```

#### Only Page Custom Model Attributes Example
```html
<oggeh method="get.page" key="{$param1}" select="model" iterate="model">
  <blockquote>
    <ul class="alt">
      <li oggeh-repeat>
        <b>{$label}</b> <span class="icon model-separator"></span> <span>{$value}</span><br />
      </li>
    </ul>
  </blockquote>
</oggeh>
```

#### Page Blocks Example
```html
<oggeh method="get.page" key="{$param1}" select="blocks">
  <!-- NOTE: always use unique block snippet parent tag (do not reuse in child tags) -->
  <article class="{$size_x}u 12u$(small)" type="rte" oggeh-snippet>
    <p oggeh-repeat>
      {$html}
    </p>
  </article>
  <article class="{$size_x}u 12u$(small)" type="media" filter="photo" oggeh-snippet>
    <div class="box alt">
      <div class="row 50% uniform">
        <!-- NOTE: always use unique block repeat parent tag (do not reuse in child tags) -->
        <div class="4u 12u$(small)" oggeh-repeat>
          <span class="image fit">
            <a href="{$regular.url}" data-title="{$caption.title}" data-lightbox="consistent">
              <img src="{$thumbnail.url}" alt="{$caption.title}" />
            </a>
            <span>{$caption.title}</span>
            <p>{$caption.description}</p>
          </span>
        </div>
      </div>
    </div>
  </article>
  <article class="{$size_x}u 12u$(small)" type="media" filter="audio" oggeh-snippet>
    <div class="box alt">
      <div class="row 50% uniform">
        <!-- NOTE: always use unique block repeat parent tag (do not reuse in child tags) -->
        <div class="4u 12u$(small)" oggeh-repeat>
          <span class="audio image fit">
            <img src="{$regular.url}" alt="{$caption.title}" />
            <audio controls>
              <source src="{$url}" type="audio/mpeg">
            </audio>
            <span>{$caption.title}</span>
            <p>{$caption.description}</p>
          </span>
        </div>
      </div>
    </div>
  </article>
  <article class="{$size_x}u 12u$(small)" type="media" filter="video" oggeh-snippet>
    <div class="box alt">
      <div class="row 50% uniform">
        <!-- NOTE: always use unique block repeat parent tag (do not reuse in child tags) -->
        <div class="4u 12u$(small)" oggeh-repeat>
          <span class="video image fit">
            <img src="{$regular.url}" alt="{$caption.title}" />
            <iframe data-src="{$url}?autoplay=1" allowfullscreen="allowfullscreen"></iframe>
            <span>{$caption.title}</span>
            <p>{$caption.description}</p>
          </span>
        </div>
      </div>
    </div>
  </article>
  <article class="{$size_x}u 12u$(small)" type="files" oggeh-snippet>
    <ul class="alt">
      <!-- NOTE: always use unique block repeat parent tag (do not reuse in child tags) -->
      <li oggeh-repeat>
        <a href="{$url}">{$caption.title}</a>
        <p>{$caption.description}</p>
      </li>
    </ul>
  </article>
  <article class="{$size_x}u 12u$(small)" type="table" oggeh-snippet>
    <div class="table-wrapper">
      <table>
        {$table}
      </table>
    </div>
  </article>
</oggeh>
```

#### Only Page Rich Text Blocks Example
```html
<oggeh method="get.page" key="{$param1}" select="blocks" block-type="rte">
  <p oggeh-repeat>
    {$html}
  </p>
</oggeh>
```

#### Only Page Photos Example
```html
<oggeh method="get.page" key="{$param1}" select="photos">
  <ul>
    <li oggeh-repeat>
      <div class="thumbnail">
        <a href="{$original.url}" data-lightbox="{$code}">
          <img alt="{$caption.title}" src="{$regular.url}" />
        </a>
        <a class="block" href="#">
          <div>
            <h5>{$caption.title}</h5>
            <p class="lead">
              {$caption.description}
            </p>
          </div>
        </a>
      </div>
    </li>
  </ul>
</oggeh>
```

#### Page Form Example
```html
<oggeh method="get.page" key="{$param1}" select="blocks" block-type="form" iterate="form">
  <form method="post" action="{$endpoint}/?api_key={$api_key}&lang={$lang}" data-success="{$oggeh-phrase|form-success}" data-error="{$oggeh-phrase|form-error}">
    <!-- NOTE: always use unique field parent tag (do not reuse in child tags) -->
    <article class="12u" type="text" subtype="text" inject="validate-required|required" oggeh-field>
      <label for="{$name}">{$label}</label>
      {$control}
    </article>
    <article class="12u$" type="select" inject="validate-required|required" oggeh-field>
      <label for="{$name}">{$label}</label>
      <div class="select-wrapper">
        {$control}
      </div>
    </article>
    <article class="4u 12u$(small)" type="radio-group" inject="validate-required|required" oggeh-field>
      {$control}
      <label for="{$name}">{$label}</label>
    </article>
    <article class="4u 12u$(small)" type="checkbox" inject="validate-required|required" oggeh-field>
      {$control}
      <!-- NOTE: conditional inline tag class inject (not applying to control) -->
      <label for="{$name}" inject="input-checkbox--switch|toggle">{$label}</label>
    </article>
    <article class="4u 12u$(small)" type="checkbox-group" inject="validate-required|required" oggeh-field>
      {$control}
      <label for="{$name}">{$label}</label>
    </article>
    <!-- TODO: shall we separate normal control class inject from conditional control class inject? -->
    <article class="12u" type="date" inject="datepicker" oggeh-field>
      <label for="{$name}">{$label}</label>
      {$control}
    </article>
    <article class="12u" type="file" oggeh-field>
      {$control}
      <label for="{$name}">{$label}</label>
    </article>
    <article class="12u" inject="validate-required|required" oggeh-field>
      <label for="{$name}">{$label}</label>
      {$control}
    </article>
    <article class="12u" oggeh-static>
      <ul class="actions">
        <li><input type="submit" value="{$oggeh-phrase|submit}" class="special" /></li>
        <li><input type="reset" value="{$oggeh-phrase|reset}" /></li>
      </ul>
    </article>
  </form>
</oggeh>
```

#### Albums Example
```html
<oggeh method="get.albums" select="media,label,cover">
  <!-- NOTE: always use unique block snippet parent tag (do not reuse in child tags) -->
  <article class="4u 12u$(small)" media="photo" oggeh-album>
    <a href="/{$lang}/album/photo/{$label}">
      <span class="album image fit" data-alt="{$cover.regular.url}">
        <img src="{$cover.regular.url}" alt="{$label}" />
        <span>{$label}</span>
      </span>
    </a>
  </article>
  <article class="4u 12u$(small)" media="audio" oggeh-album>
    <a href="/{$lang}/album/audio/{$label}">
      <span class="album image fit" data-alt="{$cover.regular.url}">
        <img src="{$cover.regular.url}" alt="{$label}" />
        <span>{$label}</span>
      </span>
    </a>
  </article>
  <article class="4u 12u$(small)" media="video" oggeh-album>
    <a href="/{$lang}/album/video/{$label}">
      <span class="album image fit" data-alt="{$cover.regular.url}">
        <img src="{$cover.regular.url}" alt="{$label}" />
        <span>{$label}</span>
      </span>
    </a>
  </article>
  <article class="4u 12u$(small)" media="file" oggeh-album>
    <a href="/{$lang}/album/file/{$label}">
      <span class="album image fit" data-alt="{$cover.regular.url}">
        <img src="{$cover.regular.url}" alt="{$label}" />
        <span>{$label}</span>
      </span>
    </a>
  </article>
</oggeh>
```

## API Documentation

See [API Reference](http://docs.oggeh.com/#reference-section) for additional details on available values for _select_ attribute on each API Method.

## Template in Use

**Template in Use**
[Editorial by HTML5 UP](https://html5up.net/editorial)

**Template License**
[Creative Commons Attribution 3.0](https://html5up.net/license)

**Template Credits**
Built by [AJ](https://twitter.com/ajlkn) - Modified by [OGGEH Cloud Computing](https://dev.oggeh.com)

### Photos used
[unsplush.com](http://unsplush.com)
