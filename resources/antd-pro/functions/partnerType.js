import { types } from "../data/partnerType";
const getPartnerByType = type => {
  return types.find(o => o.type == type);
};

export { getPartnerByType };
