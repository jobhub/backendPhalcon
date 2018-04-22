<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        {{ get_title() }}
        {{ stylesheet_link('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css') }}
        {{ stylesheet_link('css/bootstrap-theme.min.css')}}
    </head>
    <body>
        {{ content() }}
        {{ javascript_include('js/jquery.min.js') }}

        {{ javascript_include('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js') }}
        {{ javascript_include('https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js') }}
        {{ javascript_include('js/utils.js') }}
    </body>
</html>
