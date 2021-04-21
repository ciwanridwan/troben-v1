import moment from "moment";

export default [
  {
    title: "No",
    dataIndex: "number"
    // customRender: (text, row, index) => {
    //   return {
    //     children: text,
    //     attrs: {
    //       rowSpan: 2
    //     }
    //   };
    // }
  },
  {
    title: "ID Order",
    customRender: (text, row, index) => {
      return {
        children: row.code.content
      };
    }
  },
  {
    title: "Tanggal Order",
    dataIndex: "created_at",
    key: "created_at",
    colSpan: 2,
    align: "center",
    customRender: (text, row, index) => {
      return {
        attrs: {
          colSpan: 2
        },
        children: moment(text).format("ddd, DD MMM YYYY HH:mm:ss")
      };
    }
  }
];
