
{{ content() }}

<div class="page-header">
    <h2>Регистрация</h2>
</div>

{{ form('register', 'id': 'registerForm', 'onbeforesubmit': 'return false') }}

    <fieldset>

         {% for element in form %}
                    <div class="control-group">
                        {{ element.label(["class": "control-label"]) }}

                        <div class="control">
                            {{ element }}
                        </div>
                    </div>
         {% endfor %}
         <div class="form-actions">
                     {{ submit_button('Регистрация', 'class': 'btn btn-primary', 'onclick': 'return SignUp.validate();') }}
                     <!--<p class="help-block">By signing up, you accept terms of use and privacy policy.</p>-->
         </div>



    </fieldset>
</form>
