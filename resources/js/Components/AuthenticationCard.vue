<template>
  <div
    class="max-w-xl place-content-center w-full flex flex-col sm:justify-center items-center bg-white p-12"
  >
    <div>
      <slot name="logo" />
    </div>

    <div class="w-full sm:max-w-xl mt-2 pb-10 overflow-hidden sm:rounded-lg">
      <slot />
    </div>
  </div>
</template>
