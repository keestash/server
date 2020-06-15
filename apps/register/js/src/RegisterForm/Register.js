import {Validator} from "./Validator/Validator";
import {FirstName} from "./Validator/Attributes/FirstName";
import {LastName} from "./Validator/Attributes/LastName";
import {Email} from "./Validator/Attributes/Email";
import {UserName} from "./Validator/Attributes/UserName";
import {Phone} from "./Validator/Attributes/Phone";
import {Website} from "./Validator/Attributes/Website";
import {Password} from "./Validator/Attributes/Password";
import {PasswordRepeat} from "./Validator/Attributes/PasswordRepeat";
import {TermsAndConditions} from "./Validator/Attributes/TermsAndConditions";

export class Register {

    constructor(request, routes) {
        this.request = request;
        this.routes = routes;
    }

    setup() {
        const validator = new Validator();
        validator.register(new FirstName());
        validator.register(new LastName());
        validator.register(new Email());
        validator.register(new UserName());
        validator.register(new Phone());
        validator.register(new Website());
        validator.register(
            new Password(
                this.request
                , this.routes
            )
        );
        validator.register(new TermsAndConditions());
        validator.register(new PasswordRepeat());
        validator.validate();
    }

}
