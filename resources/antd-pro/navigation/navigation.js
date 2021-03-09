import _ from "lodash";
import main from "./main";

const getNavigation = (route = "", routeList = undefined) => {
  if (!_.isObject(routeList)) {
    routeList = main;
  }

  let nav = {};
  _.forIn(routeList, function(value, key) {
    if (value.route === route) {
      nav = value;
      return nav;
    }

    if (_.isObject(value.children)) {
      _.forIn(value.children, function(v, k) {
        if (v.route === route) {
          nav = v;
        }
      });
    }
  });
  return nav;
};

export default getNavigation;
