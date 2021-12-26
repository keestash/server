import {AppStorage} from "./Storage/AppStorage";
import {TemporaryStorage} from "./Storage/TemporaryStorage";
import {Parser} from "./UI/Template/Parser/Parser";
import {Routes as GlobalRoutes} from "./Route/Routes";
import {Host} from "./Backend/Host";
import {Request} from "./Backend/Request";
import {TemplateLoader} from "./Storage/TemplateStorage/TemplateLoader";
import {StringLoader} from "./Storage/StringStorage/StringLoader";
import {Router} from "./Route/Router";
import {Mini} from "./UI/Modal/Mini";
import {DateTimeService} from "./Service/DateTime/DateTimeService";
import {Confirmation} from "./UI/Modal/Confirmation";
import {Email} from "./Validation/Email";
import {Phone} from "./Validation/Phone";
import {Axios} from "./Backend/Axios";
import {Website} from "./Validation/Website";
import {ArrayListHelper} from "./Util/ArrayListHelper";
import {InputService} from "./UI/Input/InputService";
import {ButtonService} from "./UI/Button/ButtonService";
import {AssetReader} from "./Service/Asset/AssetReader";
import {UrlService} from "./Service/Http/UrlService";
import {BreadCrumbService} from "./UI/BreadCrumb/BreadCrumbService";
import {SystemService} from "./Service/System/SystemService";

export const APP_STORAGE = "appstorage.storage";
export const TEMPORARY_STORAGE = "temporarystorage.storage";
export const TEMPLATE_PARSER = "parser.parser.template.ui";
export const HOST = "host.backend";
export const ROUTER = "router.router";
export const GLOBAL_ROUTES = "routes.route";
export const REQUEST = "request.backend";
export const TEMPLATE_LOADER = "templateloader.templatestorage.storage";
export const MINI_MODAL = "mini.modal.ui";
export const STRING_LOADER = "stringloader.stringstorage.storage";
export const DATE_TIME_SERVICE = "datetimeservice.datetime.service";
export const CONFIRMATION_MODAL = "confirmation.modal.ui";
export const EMAIL_VALIDATOR = "validator.email";
export const PHONE_VALIDATOR = "validator.phone";
export const AXIOS = "axios";
export const URL_VALIDATOR = "validator.url";
export const ARRAYLIST_HELPER = "helper.arraylist";
export const INPUT_SERVICE = "service.input";
export const BUTTON_SERVICE = "service.button";
export const ASSET_READER = "reader.asset";
export const URL_SERVICE = "service.url";
export const BREADCRUMB_SERVICE = "service.breadcrumb";
export const SYSTEM_SERVICE_GLOBAL = "service.system.global";

export class StartUp {
    constructor(container) {
        this.container = container;
    }

    setUp() {

        this.container.register(
            APP_STORAGE
            , () => {
                return new AppStorage();
            }
        )
        this.container.register(
            BREADCRUMB_SERVICE
            , () => {
                return new BreadCrumbService();
            }
        )

        this.container.register(
            TEMPORARY_STORAGE
            , () => {
                return new TemporaryStorage();
            }
        )
        this.container.register(
            TEMPLATE_PARSER
            , () => {
                return new Parser();
            }
        )
        this.container.register(
            HOST
            , () => {
                return new Host();
            }
        );
        this.container.register(
            ROUTER
            , (container) => {
                return new Router(
                    container.query(HOST)
                );
            }
        );
        this.container.register(
            GLOBAL_ROUTES
            , (container) => {
                return new GlobalRoutes(
                    container.query(HOST)
                );
            }
        );
        this.container.register(
            REQUEST
            , (container) => {
                return new Request(
                    container.query(APP_STORAGE)
                    , container.query(ROUTER)
                );
            }
        );
        this.container.register(
            TEMPLATE_LOADER
            , (container) => {
                return new TemplateLoader(
                    container.query(REQUEST)
                    , container.query(GLOBAL_ROUTES)
                );
            }
        );

        this.container.register(
            MINI_MODAL
            , (container) => {
                return new Mini(
                    container.query(TEMPLATE_LOADER)
                    , container.query(TEMPLATE_PARSER)
                );
            }
        );

        this.container.register(
            CONFIRMATION_MODAL
            , (container) => {
                return new Confirmation(
                    container.query(TEMPLATE_LOADER)
                    , container.query(TEMPLATE_PARSER)
                );
            }
        );


        this.container.register(
            STRING_LOADER
            , (container) => {
                return new StringLoader(
                    container.query(REQUEST)
                    , container.query(GLOBAL_ROUTES)
                );
            }
        )

        this.container.register(
            DATE_TIME_SERVICE
            , (container) => {
                return new DateTimeService(
                    container.query(APP_STORAGE)
                )
            }
        );

        this.container.register(
            EMAIL_VALIDATOR
            , () => {
                return new Email();
            }
        )

        this.container.register(
            PHONE_VALIDATOR
            , () => {
                return new Phone();
            }
        )

        this.container.register(
            AXIOS
            , (container) => {
                return new Axios(container.query(APP_STORAGE))
            }
        )

        this.container.register(
            URL_VALIDATOR
            , () => {
                return new Website();
            }
        )

        this.container.register(
            ARRAYLIST_HELPER
            , () => {
                return new ArrayListHelper();
            }
        )

        this.container.register(
            INPUT_SERVICE
            , () => {
                return new InputService();
            }
        )

        this.container.register(
            BUTTON_SERVICE
            , () => {
                return new ButtonService();
            }
        );
        this.container.register(
            URL_SERVICE
            , () => {
                return new UrlService();
            }
        );

        this.container.register(
            SYSTEM_SERVICE_GLOBAL
            , () => {
                return new SystemService();
            }
        );

        this.container.register(
            ASSET_READER
            , (container) => {
                return new AssetReader(
                    container.query(STRING_LOADER)
                    , container.query(TEMPLATE_LOADER)
                )
            }
        );

    }

    getContainer() {
        return this.container;
    }
}
