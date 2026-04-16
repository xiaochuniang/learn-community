import { ref } from 'vue'
import type { FormInstance } from 'element-plus'

/**
 * 通用表单 Dialog Hook
 */
export function useFormDialog<T extends object>(defaultForm: T) {
  const visible = ref(false)
  const submitting = ref(false)
  const isEdit = ref(false)
  const form = ref<T>({ ...defaultForm })
  const formRef = ref<FormInstance>()

  function open(data?: Partial<T>) {
    isEdit.value = !!data
    form.value = data ? ({ ...defaultForm, ...data } as T) : ({ ...defaultForm } as T)
    visible.value = true
  }

  function close() {
    visible.value = false
    formRef.value?.resetFields()
  }

  async function submit(handler: (data: T) => Promise<void>) {
    if (!formRef.value) return
    await formRef.value.validate(async (valid) => {
      if (!valid) return
      submitting.value = true
      try {
        await handler(form.value as T)
        close()
      } finally {
        submitting.value = false
      }
    })
  }

  return { visible, submitting, isEdit, form, formRef, open, close, submit }
}
