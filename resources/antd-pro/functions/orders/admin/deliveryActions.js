const getComponentByTypeAndStatus = (actions, type, status) => {
  let action = actions.find(
    o => o.type.indexOf(type) > -1 && o.status.indexOf(status) > -1
  );
  return action;
};

export { getComponentByTypeAndStatus };
