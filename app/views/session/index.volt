
{{ content() }}

<div class="page-header">
    <h2>Register</h2>
</div>

{{ form('session/start', 'id': 'AuthorizedForm', 'onbeforesubmit': 'return false') }}

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
                     {{ submit_button('Войти', 'class': 'btn btn-primary', 'onclick': 'return SignUp.validate();') }}
                 </div>

    </fieldset>
</form>
