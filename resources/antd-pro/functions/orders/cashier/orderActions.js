import { actions } from "../../../data/order/cashier/orderActions";

const getComponentByStatusAndPaymentStatus = (status, payment_status) => {
  let action = actions.find(
    o =>
      o.status.indexOf(status) > -1 &&
      o.payment_status.indexOf(payment_status) > -1
  );

  return action;
};

export { getComponentByStatusAndPaymentStatus };
