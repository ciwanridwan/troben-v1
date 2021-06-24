import { statuses, types } from "../data/packageStatus";

const getMessageByStatus = status => {
  let message = "";

  let statusSelected = statuses.find(o => o.status == status);

  message += statusSelected ? statusSelected.message : "Untracked Type";
  return {
    messageType: statusSelected?.messageType,
    message: message
  };
};
const getPackageOrderType = type => {
  return types.find(o => o.type == type);
};

export { getMessageByStatus, getPackageOrderType };
