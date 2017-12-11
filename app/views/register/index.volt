
{{ content() }}

<div class="page-header">
    <h2>Register</h2>
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
                     {{ submit_button('Register', 'class': 'btn btn-primary', 'onclick': 'return SignUp.validate();') }}
                     <p class="help-block">By signing up, you accept terms of use and privacy policy.</p>
                 </div>

        <!--<div class="control-group">
            {{ form.label('email', ['class': 'control-label']) }}
            <div class="controls">
                {{ form.render('email', ['class': 'form-control']) }}
                <p class="help-block">(Обязательно)</p>
                <div class="alert alert-warning" id="email_alert">
                    Укажите e-mail
                </div>
            </div>
        </div>

        <div class="control-group">
                    {{ form.label('phone', ['class': 'control-label']) }}
                    <div class="controls">
                        {{ form.render('phone', ['class': 'form-control']) }}
                        <p class="help-block">(Обязательно)</p>
                        <div class="alert alert-warning" id="email_alert">
                            Введите, пожалуйста, номер телефона
                        </div>
                    </div>
                </div>

        <div class="control-group">
            {{ form.label('password', ['class': 'control-label']) }}
            <div class="controls">
                {{ form.render('password', ['class': 'form-control']) }}
                <p class="help-block">(minimum 8 characters)</p>
                <div class="alert alert-warning" id="password_alert">
                    Придумайте пароль
                </div>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="repeatPassword">Repeat Password</label>
            <div class="controls">
                {{ password_field('repeatPassword', 'class': 'form-control') }}
                <div class="alert" id="repeatPassword_alert">
                    Подтвердите введенный пароль
                </div>
            </div>
        </div>

        <div class="form-actions">
            {{ submit_button('Register', 'class': 'btn btn-primary', 'onclick': 'return SignUp.validate();') }}
            <p class="help-block">By signing up, you accept terms of use and privacy policy.</p>
        </div>-->

    </fieldset>
</form>
