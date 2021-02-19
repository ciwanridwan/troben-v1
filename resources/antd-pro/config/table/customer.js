export default [
  {
    title: '#',
    key: 'id',
    scopedSlots: { customRender: 'number' },
  },
  {
    title: 'Name',
    dataIndex: 'name',
    key: 'name',
    scopedSlots: { customRender: 'name' },
  },
  {
    title: 'Phone',
    key: 'phone',
    scopedSlots: { customRender: 'phone' },
  },
  {
    title: 'Email',
    key: 'email',
    scopedSlots: { customRender: 'email' },
  },
  {
    title: 'Action',
    key: 'action',
    align: 'center',
    width: '100px',
    scopedSlots: { customRender: 'action' },
  },
]
