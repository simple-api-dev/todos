<style>
    body{
        font-family: Arial, serif;
    }
    pre{
        font-size: 14pt;
        text-align: center;
        background-color: #dddddd;
        padding: 5px;
    }
    h2{
        text-align: center;
        background-color: #dddddd;
        padding: 5px;
    }
</style>
<h1>TodoAPI Developer Account {{$type}}</h1>
<p>Thanks for using TodoApi.  {{$type != "Deletion Request" ? "Your api key is:" : ""}}</p>


@if($type!="Deletion Request")
    <pre>{{$apikey}}</pre>
    <p>For full api documentation,
        please visit <a href="{{$doc_url}}/docs">{{$doc_url}}/docs</a></p>
@else
    <p>Please return to our site and provide the below code to complete your account deletion</p>
    <h2>{{$apikey}}</h2>
@endif
