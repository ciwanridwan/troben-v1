function isPromise(promise) {
  return !!promise && typeof promise.then === "function";
}

export { isPromise };
