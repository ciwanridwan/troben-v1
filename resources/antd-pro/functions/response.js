import { responseMessage } from "../data/response";

const getMessageByCode = code => {
  return responseMessage.find(o => o.code === code);
};

export { getMessageByCode };
