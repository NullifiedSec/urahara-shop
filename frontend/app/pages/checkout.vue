<template>
  <div class="container mx-auto px-4 py-8 max-w-4xl">
    <h1 class="text-4xl font-bold mb-8">Checkout</h1>

    <form @submit.prevent="handleSubmit" class="space-y-8">
      <!-- Shipping Address -->
      <section>
        <h2 class="text-2xl font-semibold mb-4">Shipping Address</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block mb-2">Full Name</label>
            <input
              v-model="form.shipping_address.name"
              type="text"
              required
              class="w-full px-4 py-2 border rounded-lg"
            />
          </div>
          <div>
            <label class="block mb-2">Phone</label>
            <input
              v-model="form.shipping_address.phone"
              type="tel"
              required
              class="w-full px-4 py-2 border rounded-lg"
            />
          </div>
          <div class="md:col-span-2">
            <label class="block mb-2">Address</label>
            <input
              v-model="form.shipping_address.address"
              type="text"
              required
              class="w-full px-4 py-2 border rounded-lg"
            />
          </div>
          <div>
            <label class="block mb-2">City</label>
            <input
              v-model="form.shipping_address.city"
              type="text"
              required
              class="w-full px-4 py-2 border rounded-lg"
            />
          </div>
          <div>
            <label class="block mb-2">State</label>
            <input
              v-model="form.shipping_address.state"
              type="text"
              required
              class="w-full px-4 py-2 border rounded-lg"
            />
          </div>
          <div>
            <label class="block mb-2">ZIP Code</label>
            <input
              v-model="form.shipping_address.zip"
              type="text"
              required
              class="w-full px-4 py-2 border rounded-lg"
            />
          </div>
          <div>
            <label class="block mb-2">Country</label>
            <input
              v-model="form.shipping_address.country"
              type="text"
              required
              class="w-full px-4 py-2 border rounded-lg"
            />
          </div>
        </div>
      </section>

      <!-- Order Notes -->
      <section>
        <h2 class="text-2xl font-semibold mb-4">Order Notes (Optional)</h2>
        <textarea
          v-model="form.notes"
          rows="4"
          class="w-full px-4 py-2 border rounded-lg"
          placeholder="Any special instructions for your order..."
        ></textarea>
      </section>

      <!-- Order Summary -->
      <section>
        <h2 class="text-2xl font-semibold mb-4">Order Summary</h2>
        <div v-if="cart" class="p-6 border rounded-lg space-y-2">
          <div class="flex justify-between">
            <span>Subtotal</span>
            <span>${{ cart.subtotal }}</span>
          </div>
          <div class="flex justify-between">
            <span>Tax</span>
            <span>$0.00</span>
          </div>
          <div class="flex justify-between">
            <span>Shipping</span>
            <span>$0.00</span>
          </div>
          <div class="border-t pt-2 flex justify-between font-semibold text-lg">
            <span>Total</span>
            <span>${{ cart.total }}</span>
          </div>
        </div>
      </section>

      <!-- Submit Button -->
      <button
        type="submit"
        :disabled="isSubmitting"
        class="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
      >
        {{ isSubmitting ? 'Processing...' : 'Place Order' }}
      </button>
    </form>
  </div>
</template>

<script setup lang="ts">
const router = useRouter()

// Fetch cart
const { data: cart } = await useAsyncData('cart', () => cartApi.get())

// Form state
const form = reactive({
  shipping_address: {
    name: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    zip: '',
    country: '',
  },
  notes: '',
})

const isSubmitting = ref(false)

// Handle form submission
async function handleSubmit() {
  if (!cart?.items || cart.items.length === 0) {
    alert('Your cart is empty')
    return
  }

  isSubmitting.value = true

  try {
    const order = await orderApi.create({
      shipping_address: form.shipping_address,
      notes: form.notes || undefined,
    })

    // Redirect to order confirmation page
    router.push(`/orders/${order.id}`)
  } catch (error) {
    console.error('Failed to create order:', error)
    alert('Failed to place order. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}

useHead({
  title: 'Checkout - UraharaShop',
})
</script>

