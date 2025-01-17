// success group 0000-0099
const RC_SUCCESS = "0000";
const RC_CREATED = "0001";
const RC_UPDATED = "0002";
const RC_ACCEPTED = "0003";
const RC_ACCEPTED_NO_CONTENT = "0004";

// client side fault. 0100 - 0199
const RC_INVALID_DATA = "0100";
const RC_RESOURCE_NOT_FOUND = "0101";
const RC_ROUTE_NOT_FOUND = "0102";
const RC_INVALID_PHONE_NUMBER = "0103";
const RC_OUT_OF_RANGE = "0104";

// authentication / authorization related. 0200 - 0299
const RC_UNAUTHENTICATED = "0200";
const RC_INVALID_AUTHENTICATION_HEADER = "0201";
const RC_MISSING_AUTHENTICATION_HEADER = "0202";
const RC_ACCOUNT_NOT_VERIFIED = "0203";
const RC_UNAUTHORIZED = "0204";

// one time password 0300 - 0399
const RC_MISMATCH_TOKEN_OWNERSHIP = "0301";
const RC_TOKEN_HAS_EXPIRED = "0302";
const RC_TOKEN_MISMATCH = "0303";
const RC_TOKEN_WAS_CLAIMED = "0304";
const RC_SMS_GATEWAY_WAS_BROKEN = "0305";

// partner error
const RC_PARTNER_GEO_UNAVAILABLE = "0401";

// server side faults. 0900 - 0999
const RC_SERVER_IN_MAINTENANCE = "0901";
const RC_DATABASE_ERROR = "0902";
const RC_OTHER = "0999";

const responseMessage = [
  {
    code: RC_OUT_OF_RANGE,
    message: "Sorry, we are haven't provide your destination"
  },
  {
    code: RC_INVALID_DATA,
    message: "Sorry, your data is invalid. please check your data"
  },
  {
    code: RC_INVALID_PHONE_NUMBER,
    message: "Sorry, your phone was invalid"
  },
  {
    code: RC_PARTNER_GEO_UNAVAILABLE,
    message: "please complete your partner geo information"
  }
];
export {
  responseMessage,
  RC_SUCCESS,
  RC_CREATED,
  RC_UPDATED,
  RC_ACCEPTED,
  RC_ACCEPTED_NO_CONTENT,
  RC_INVALID_DATA,
  RC_RESOURCE_NOT_FOUND,
  RC_ROUTE_NOT_FOUND,
  RC_INVALID_PHONE_NUMBER,
  RC_OUT_OF_RANGE,
  RC_UNAUTHENTICATED,
  RC_INVALID_AUTHENTICATION_HEADER,
  RC_MISSING_AUTHENTICATION_HEADER,
  RC_ACCOUNT_NOT_VERIFIED,
  RC_UNAUTHORIZED,
  RC_MISMATCH_TOKEN_OWNERSHIP,
  RC_TOKEN_HAS_EXPIRED,
  RC_TOKEN_MISMATCH,
  RC_TOKEN_WAS_CLAIMED,
  RC_SMS_GATEWAY_WAS_BROKEN,
  RC_SERVER_IN_MAINTENANCE,
  RC_DATABASE_ERROR,
  RC_OTHER
};
