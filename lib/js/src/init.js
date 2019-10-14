import ProfileImage from "./Observer/ProfileImage";
import NewPart from "./Observer/NewPart";
import AppNavigationItemClick from "./Observer/AppNavigationItemClick";
import AppNavigationItemSubMenu from "./Observer/AppNavigationItemSubMenu";
import BreadCrumbParser from "./BreadCrumb/BreadCrumbParser";

if (!window.Keestash) {
    window.Keestash = {};
}
if (!window.Keestash.Main) {
    window.Keestash.Main = {};
}

if (!window.Keestash.Apps) {
    window.Keestash.Apps = {};
}

if (!window.Keestash.Observer) {
    window.Keestash.Observer = {};
}

if (!window.Keestash.Observer.ProfileImage) {
    window.Keestash.Observer.ProfileImage = ProfileImage;
}

if (!window.Keestash.Observer.NewPart) {
    window.Keestash.Observer.NewPart = NewPart;
}
if (!window.Keestash.Observer.AppNavigationItemClick) {
    window.Keestash.Observer.AppNavigationItemClick = AppNavigationItemClick;
}
if (!window.Keestash.Observer.AppNavigationItemSubMenu) {
    window.Keestash.Observer.AppNavigationItemSubMenu = AppNavigationItemSubMenu;
}

if (!window.Keestash.Observer.BreadCrumbParser) {
    window.Keestash.Observer.BreadCrumbParser = BreadCrumbParser;
}