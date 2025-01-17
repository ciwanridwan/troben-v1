import { getComponentByTypeAndStatus } from "./admin/deliveryActions";
import { getComponentByStatusAndPaymentStatus } from "./cashier/orderActions";

const getOriginAddress = selectedPackage => {
  let address = [
    selectedPackage?.origin_sub_district?.name,
    selectedPackage?.origin_district?.name,
    selectedPackage?.origin_regency?.name
  ];
  address = address.join(" ");
  address += ", " + selectedPackage?.origin_regency?.name;
  return address;
};
const getDestinationAddress = selectedPackage => {
  let address = [
    selectedPackage?.destination_district?.name,
    selectedPackage?.destination_sub_district?.name
  ];
  address = address.join(" ");
  address += ", " + selectedPackage?.destination_regency?.name;
  return address;
};
const getServicePrice = item => {
  let price = item?.prices?.find(o => o.type.toLowerCase() === "service");
  return price?.amount;
};
const getInsurancePrice = prices => {
  let price = prices?.find(o => o.type.toLowerCase() === "insurance");
  return price?.amount || 0;
};
const getHandlingPrice = item => {
  let price = 0;
  let handlings = item?.prices?.filter(
    o => o.type.toLowerCase() === "handling"
  );
  handlings.forEach(handling => {
    price += handling.amount;
  });
  return price;
};
const getHandlings = item => {
  return item?.handling?.filter(o => o.type.toLowerCase() === "handling");
};
const getTierPrice = items => {
  return items ? items[0]?.tier_price : null;
};
const getTotalWeightBorne = items => {
  let totalWeightBorne = 0;
  items.forEach(item => {
    totalWeightBorne += item.weight_borne_total;
  });
  return totalWeightBorne;
};
const getSubTotalItem = item => {
  let subTotal = 0;
  item?.prices?.forEach(price => {
    subTotal += price.amount * item.qty;
  });
  return subTotal;
};
const getSubTotalItems = items => {
  let subTotal = 0;
  items.forEach(item => {
    subTotal += getSubTotalItem(item);
  });
  return subTotal;
};
const getNumberOfItems = items => {
  let numberItem = 0;
  items.forEach(item => {
    numberItem += item.qty;
  });
  return numberItem;
};

export {
  getComponentByTypeAndStatus,
  getComponentByStatusAndPaymentStatus,
  getHandlingPrice,
  getInsurancePrice,
  getServicePrice,
  getTierPrice,
  getTotalWeightBorne,
  getSubTotalItems,
  getHandlings,
  getOriginAddress,
  getDestinationAddress,
  getNumberOfItems
};
