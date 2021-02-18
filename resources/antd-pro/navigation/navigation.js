import main from "./main";

const getNavigation = (route = "") => {
  let nav = {};
  _.forIn(main, function(value, key) {
    if (value.route === route) {
      nav = value;
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
