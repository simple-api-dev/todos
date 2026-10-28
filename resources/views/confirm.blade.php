<style>
    body{
        font-family: Arial, serif;
    }
    h2{
        text-align: center;
        background-color: #dddddd;
        padding: 5px;
    }
</style>
<h1>Confirm your email address</h1>
<p>Hi {{$name}}, someone registered the admin account of an app built on SimpleAPI with this email address (the one the apikey belongs to).  To finish, send this code to the API:</p>
<h2>{{$code}}</h2>
<p>It expires in {{$minutes}} minutes and can be used once.  If you did not do this, you can ignore this email and nothing is created.</p>
