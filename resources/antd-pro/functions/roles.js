import { fusionRoles, baseRouteNameRoles } from "../data/roles";

const getAvailableFusionRoleRoutes = role => {
  let roles = fusionRoles?.find(o => o.indexOf(role) > -1);
  let routes = baseRouteNameRoles?.filter(o => roles?.indexOf(o.role) > -1);
  return routes;
};

export { getAvailableFusionRoleRoutes };
