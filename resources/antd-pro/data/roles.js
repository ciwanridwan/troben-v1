const ROLE_OWNER = "owner";
const ROLE_DRIVER = "driver";
const ROLE_CASHIER = "cashier";
const ROLE_CS = "customer-service";
const ROLE_WAREHOUSE = "warehouse";

const baseRouteNameRoles = [
  {
    role: ROLE_CASHIER,
    title: "Cashier",
    baseRouteName: "partner.cashier.home"
  },
  {
    role: ROLE_CS,
    title: "Customer Service",
    baseRouteName: "partner.customer_service.home"
  }
];

const fusionRoles = [[ROLE_CASHIER, ROLE_CS]];

export {
  ROLE_OWNER,
  ROLE_DRIVER,
  ROLE_CASHIER,
  ROLE_CS,
  ROLE_WAREHOUSE,
  baseRouteNameRoles,
  fusionRoles
};
