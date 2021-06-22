import { statuses } from "../data/packageStatus";

const getMessageByStatus = status => {
  let message = "";

  let statusSelected = statuses.find(o => o.status == status);

  message += statusSelected ? statusSelected.message : "Untracked Type";
  return {
    messageType: statusSelected?.messageType,
    message: message
  };
};

export { getMessageByStatus };
