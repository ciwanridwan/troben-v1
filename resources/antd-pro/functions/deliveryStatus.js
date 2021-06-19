import { statuses, types } from "../data/deliveryStatus";

const getMessageByTypeStatus = (type, status) => {
  let message = "";
  let typeSelected = types.find(o => o.type == type);
  let statusSelected = statuses.find(o => o.status == status);
  message += typeSelected ? typeSelected.message : "Untracked Type";
  message += " ";
  message += statusSelected ? statusSelected.message : "Untracked Type";
  return {
    messageType: statusSelected?.messageType,
    message: message
  };
};

export { getMessageByTypeStatus };
