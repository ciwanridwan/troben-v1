import moment from "moment";

export default [
  {
    title: "No",
    dataIndex: "number",
    colspan: 2,
    classes: ["trawl-text-center"]
  },
  {
    title: "ID Order",
    customRender: (text, row, index) => {
      return {
        children: row.code.content
      };
    },
    classes: ["trawl-text-left"]
  },
  {
    title: "Tanggal Order",
    dataIndex: "created_at",
    key: "created_at",
    align: "center",
    customRender: (text, row, index) => {
      return {
        children: moment(text).format("ddd, DD MMM YYYY HH:mm:ss")
      };
    },
    classes: ["trawl-text-left"]
  }
];
