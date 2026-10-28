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
<h1>Password reset</h1>
<p>Hi {{$name}}, someone asked to reset your password for an app built on the SimpleAPI Todo API.</p>
<p>Your reset code is:</p>
<h2>{{$code}}</h2>
<p>It expires in {{$minutes}} minutes and can be used once. If you did not ask for this, you can ignore this email.</p>
