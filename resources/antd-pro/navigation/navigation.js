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

const findNestedNavigation = (route = "", routeList = undefined) => {
  let foundRoute = null;
  let findNestedNavigationFn = (route = "", routeList = undefined) =>
    _.forIn(routeList, function(value, key) {
      if (foundRoute != null) {
        return false;
      }
      if (value.route === route) {
        foundRoute = value;
      }

      if (_.isObject(value.children)) {
        findNestedNavigationFn(route, value.children);
      }
    });
  findNestedNavigationFn(route, routeList);
  return foundRoute;
};

const getParent = (route = "", routeList = undefined) => {
  let parent = null;
  _.forIn(routeList, (value, key) => {
    if (_.isObject(value.children)) {
      let foundEl = findNestedNavigation(route, value.children);
      if (foundEl != null) {
        parent = value;
      }
    }
  });
  return parent;
};

export { getNavigation, getParent };
